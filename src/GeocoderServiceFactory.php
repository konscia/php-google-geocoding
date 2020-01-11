<?php

namespace Konscia\GoogleGeocoding;

use League\CLImate\CLImate;

class GeocoderServiceFactory
{
    static function getInstance(CLImate $climate): GeocoderService
    {
        $config = require __DIR__ . '/../config.php';
        $client = new GeocoderClient($config['GOOGLE-API-KEY']);
        return new GeocoderService($client, $climate);
    }
}