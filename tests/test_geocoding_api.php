<?php

use League\CLImate\CLImate;

require_once __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/../config.php';

$climate = new CLImate();

$address = urlencode('Rua Pastor William, 1200');
$key = $config['GOOGLE-API-KEY'];
$url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$key}";
$content = file_get_contents($url);
$obj = json_decode($content, true);

$status = $obj['status'];
switch ($status) {
    case 'OK':
        $climate->green('Sucesso');
        $results = $obj['results'];
        $climate->dump($results);
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


