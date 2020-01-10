<?php

namespace Konscia\GoogleGeocoding;

class Geocoder
{
    const COMPONENT_COUNTRY = 'country';
    const COMPONENT_ADMINISTRATIVE = 'administrative_area';
    const COMPONENT_POSTAL = 'postal_code';

    /** @var GeocoderClient */
    private $client;

    public function __construct(GeocoderClient $client)
    {
        $this->client = $client;
    }

    public function addressToGeocoded(Address $address, $district): ?AddressGeocoded
    {
        $geocoded = $this->geocode($address);
        if($geocoded instanceof AddressGeocoded) {
            $geocoded->strategyToGeocoded = 'Usando endereço';
            return $geocoded;
        }

        //tenta novamente somente com o bairro
        $address->address = $district;
        $geocoded = $this->geocode($address);
        if ($geocoded instanceof AddressGeocoded) {
            $geocoded->strategyToGeocoded = 'Usando bairro';
            return $geocoded;
        }

        //tenta sem endereço para pegar somente pelo CEP
        $address->address = '';
        $geocoded = $this->geocode($address);
        if ($geocoded instanceof AddressGeocoded) {
            $geocoded->strategyToGeocoded = 'Usando cep';
            return $geocoded;
        }

        return null;
    }

    private function geocode(Address $addressObj, $retry = false): ?AddressGeocoded
    {
        $components = $this->buildComponents($addressObj);
        try {
            $obj = $this->client->get($addressObj->address, $components);
        } catch (GeocoderClientException $e) {
            if($retry) {
                return null;
            }
            //previne alguma falha de rede
            sleep(1);
            $this->geocode($addressObj, true);
        }

        $status = $obj['status'];
        switch ($status) {
            case 'OK':
                $result = $obj['results'][0];

                $geometry = $result['geometry'];
                $location = $geometry['location'];

                $geo = new AddressGeocoded();
                $geo->type = $result['types'][0];
                $geo->place_id = $result['place_id'];
                $geo->partial_match = $result['partial_match'] ?? false;
                $geo->location_type = $geometry['location_type'];
                $geo->formatted_address = $result['formatted_address'];
                $geo->lat = $location['lat'];
                $geo->lng = $location['lng'];

                return $geo;
            case 'ZERO_RESULTS':
                return null;
            case 'OVER_QUERY_LIMIT':
            case 'OVER_DAILY_LIMIT':
            case 'REQUEST_DENIED':
            case 'INVALID_REQUEST':
            case 'UNKNOWN_ERROR':
            default:
                throw new GeocodingException($status, $obj['error_message'] ?? null);
        }
    }

    public function buildComponents(Address $addressObj): array
    {
        $components = [
            self::COMPONENT_COUNTRY => 'BR',
            self::COMPONENT_ADMINISTRATIVE => $addressObj->cityName . ',' . $addressObj->uf,
        ];

        if (is_string($addressObj->postalCode) && strlen($addressObj->postalCode) === 8) {
            $components[self::COMPONENT_POSTAL] = $addressObj->postalCode;
        }

        return $components;
    }
}