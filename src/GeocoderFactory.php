<?php

namespace Konscia\GoogleGeocoding;

class GeocoderFactory
{
    static function getInstance(): Geocoder
    {
        $config = require __DIR__ . '/../config.php';
        $client = new GeocoderClient($config['GOOGLE-API-KEY']);
        return new Geocoder($client);
    }
}