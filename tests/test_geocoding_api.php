<?php

use Konscia\GoogleGeocoding\Address;
use Konscia\GoogleGeocoding\GeocoderFactory;
use League\CLImate\CLImate;

require_once __DIR__ . '/../vendor/autoload.php';


$climate = new CLImate();

$address = new Address();
$address->address = 'Rua Pastor William, 1200';
$address->uf = 'SC';
$address->cityName = 'Florianópolis';
$address->postalCode = '88034100';

$geocoder = GeocoderFactory::getInstance();
$geocoded = $geocoder->addressToGeocoded($address);

$climate->dump($geocoded);
