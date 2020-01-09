<?php

use League\CLImate\CLImate;

require_once __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/../config.php';

$climate = new CLImate();

$address = urlencode('Rua Pastor William, 1200');
$components = [
    'country' => 'BR',
    'administrative_area' => 'Florianópolis, SC',
    'postal_code' => '88034100'
];
$componentsUrl = urlencode(str_replace('=', ':', http_build_query($components, null, '|')));
$key = $config['GOOGLE-API-KEY'];
$url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&components={$componentsUrl}&key={$key}";
$content = file_get_contents($url);
$obj = json_decode($content, true);

$status = $obj['status'];
switch ($status) {
    case 'OK':
        $climate->green('Sucesso');
        $result = $obj['results'][0];

        $geometry = $result['geometry'];
        $location = $geometry['location'];

        $relevantData = [
            'type' => $result['types'][0],
            'place_id' => $result['place_id'],
            'partial_match' => $result['partial_match'] ?? false,
            'location_type' => $geometry['location_type'],
            'formatted_address' => $result['formatted_address'],
            'lat' => $location['lat'],
            'lng' => $location['lng'],
        ];

        $climate->dump($relevantData);
        break;
    case 'ZERO_RESULTS':
        $climate->info('Nenhum resultado encontrado');
        break;
    case 'OVER_QUERY_LIMIT':
    case 'OVER_DAILY_LIMIT':
    case 'REQUEST_DENIED':
    case 'INVALID_REQUEST':
    case 'UNKNOWN_ERROR':
    default:
        $climate->error('Requisição negada. Verifique o erro');
        $climate->error('Status: ' . $status);
        if(isset($obj['error_message'])) {
            $climate->error('Mensagem de erro: ' . $obj['error_message']);
        }
        break;
}


