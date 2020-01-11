<?php

use Konscia\GoogleGeocoding\AddressDTO;
use Konscia\GoogleGeocoding\GeocoderServiceFactory;
use League\CLImate\CLImate;

require_once __DIR__ . '/../vendor/autoload.php';


$climate = new CLImate();

$address = new AddressDTO();
$address->address = 'LINHA CACHOEIRINHA';
$address->uf = 'RS';
$address->cityName = 'ENGENHO VELHO';
$address->postalCode = '99698000';
$address->localName = 'ESCOLA MUNIC. ENSINO FUND. EPITACIO PESSOA - DESATIVADA';

$geocoder = GeocoderServiceFactory::getInstance($climate);
$geocoded = $geocoder->addressToGeocoded($address);

$climate->dump($geocoded);
