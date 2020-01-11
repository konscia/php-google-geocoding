<?php

namespace Konscia\GoogleGeocoding;

use League\CLImate\CLImate;

class GeocoderService
{
    const COMPONENT_COUNTRY = 'country';
    const COMPONENT_ADMINISTRATIVE = 'administrative_area';

    /** @var GeocoderClient */
    private $client;

    /** @var CLImate */
    private $climate;

    public function __construct(GeocoderClient $client, CLImate $climate)
    {
        $this->client = $client;
        $this->climate = $climate;
    }

    public function addressToGeocoded(AddressDTO $address): ?AddressGeocodedDTO
    {
        $geocoded = $this->geocode($address);
        if($geocoded instanceof AddressGeocodedDTO) {
            $geocoded->strategyToGeocoded = 'endereço';
            return $geocoded;
        }

        return null;
    }

    private function geocode(AddressDTO $addressObj, $retry = false): ?AddressGeocodedDTO
    {
        $components = $this->buildComponents($addressObj);
        $address = $this->buildAddress($addressObj);

        try {
            $obj = $this->client->get($address, $components);
        } catch (GeocoderClientException $e) {
            $this->climate->error('Erro na gecodificação: ' . $e->getMessage());
            if($retry) {
                $this->climate->error('2 erros verificados, ignorando registro');
                return null;
            }
            $this->retryGeocode($addressObj);
        }

        $status = $obj['status'];
        switch ($status) {
            case 'OK':
                $result = $obj['results'][0];
                return $this->buildAddressGeocoded($result);
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

    private function buildAddress(AddressDTO $addressObj): string
    {
        $address = [
            $addressObj->cityName,
            $addressObj->uf
        ];

        if (is_string($addressObj->postalCode) && strlen($addressObj->postalCode) === 8) {
            $address[] = $addressObj->postalCode;
        }

        $address[] = $addressObj->address;
        $address[] = $addressObj->localName;

        $address = implode(',', $address);
        return $address;
    }

    private function buildComponents(AddressDTO $addressObj): array
    {
        return [
            self::COMPONENT_COUNTRY => 'BR',
            self::COMPONENT_ADMINISTRATIVE => $addressObj->cityName . ',' . $addressObj->uf,
        ];
    }

    private function retryGeocode(AddressDTO $addressObj): void
    {
        $this->climate->error('Tentando novamente em 1 segundo');
        sleep(1); //previne alguma falha de rede
        $this->geocode($addressObj, true);
    }

    private function buildAddressGeocoded($result): AddressGeocodedDTO
    {
        $cityComponent = array_filter($result['address_components'], function ($comp) {
            return $comp['types'] == ["administrative_area_level_2", "political"];
        });

        $districtComponent = array_filter($result['address_components'], function ($comp) {
            return $comp['types'] == ["political", "sublocality", "sublocality_level_1"];
        });

        $geometry = $result['geometry'];
        $location = $geometry['location'];

        $geo = new AddressGeocodedDTO();
        $geo->type = $result['types'][0];
        $geo->place_id = $result['place_id'];
        $geo->partial_match = $result['partial_match'] ?? false;
        $geo->location_type = $geometry['location_type'];
        $geo->formatted_address = $result['formatted_address'];
        $geo->lat = $location['lat'];
        $geo->lng = $location['lng'];
        $geo->city_long_name = count($cityComponent) > 0 ? array_pop($cityComponent)['long_name'] : '';
        $geo->district_long_name = count($districtComponent) > 0 ? array_pop($districtComponent)['long_name'] : '';

        return $geo;
    }
}