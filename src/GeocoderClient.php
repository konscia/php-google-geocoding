<?php

namespace Konscia\GoogleGeocoding;

class GeocoderClient
{
    /** @var string */
    private $apiKey;

    /** @var string  */
    private $uriBase = 'https://maps.googleapis.com/maps/api/geocode';

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function get(string $addressUrl, array $components = []): array
    {
        $addressUrl = urlencode($addressUrl);
        $componentsUrl = urlencode($this->convertGoogleComponentsFormat($components));
        $url = "{$this->uriBase}/json?address={$addressUrl}&components={$componentsUrl}&key={$this->apiKey}";
        $content = file_get_contents($url);
        $obj = json_decode($content, true);

        if(is_null($obj)) {
            throw new GeocoderClientException("Erro na url: {$url}. Erro no conteúdo: {$content}. Mensagem: " . json_last_error_msg());
        }

        return $obj;
    }

    public function convertGoogleComponentsFormat(array $components): string
    {
        return str_replace('=', ':', http_build_query($components, null, '|'));
    }
}