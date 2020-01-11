<?php

use Konscia\GoogleGeocoding\AddressDTO;
use Konscia\GoogleGeocoding\GeocoderServiceFactory;
use League\CLImate\CLImate;
use League\CLImate\Exceptions\Exception as ClimateException;
use League\Csv\Exception as CsvException;
use League\Csv\Reader;
use League\Csv\Writer;

require_once __DIR__ . '/vendor/autoload.php';
$config = require __DIR__ . '/config.php';

/* -------------------- */
/* ---- CONSTANTES ---- */
/* -------------------- */

const CSV_DELIMITER = ",";
const CSV_HEADER = [
    "id",
    "uf",
    "cidade",
    "cep",
    "endereco",
    "nome_local"
];

/* ------------------- */
/* ---- ARGUMENTS ---- */
/* ------------------- */

$climate = new CLImate();
$climate->arguments->add([
    'path' => [
        'description' => 'Caminho do csv a ser importado',
        'required' => true,
    ],
    'name' => [
        'description' => 'Nome do arquivo final',
        'required' => false,
    ],
]);

try {
    $climate->arguments->parse();
} catch (\League\CLImate\Exceptions\InvalidArgumentException $e) {
    $climate->to('error')
        ->red($e->getMessage())
        ->usage();
    exit;
}

try {

    /* ------------------ */
    /* ---- READ CSV ---- */
    /* ------------------ */
    $climate->info('Iniciando processamento do arquivo: ' . $path);

    $path = $climate->arguments->get('path');
    if (!file_exists($path)) {
        $climate->error('Arquivo não localizado: ' . $path);
        exit;
    }

    /** @var \League\Csv\AbstractCsv $csv */
    $csv = Reader::createFromPath($path, 'r');
    $csv->setDelimiter(CSV_DELIMITER);
    $csv->setHeaderOffset(0);
    $header = $csv->getHeader();

    $climate->info('Validando cabeçalho');
    if($header != CSV_HEADER) {
        $climate
            ->error('Cabeçalho do CSV não compatível.')
            ->error('O cabeçalho deve ter as seguintes colunas:')
            ->columns(CSV_HEADER, 1);
        exit;
    }

    /* -------------------- */
    /* ---- WRITE FILE ---- */
    /* -------------------- */

    $climate->info('Criação do arquivo de saída');
    $name = $climate->arguments->get('name') ?: time();
    $writer = Writer::createFromPath(__DIR__ . "/output/{$name}.csv", 'w+');
    $writer->setDelimiter(CSV_DELIMITER);
    $writerHeader = array_merge($header, [
        'g_type',
        'g_location_type',
        'g_formatted_address',
        'g_lat',
        'g_lng',
        'g_city_long_name',
        'g_district_long_name',
        'g_strategy_to_geocoded',
    ]);
    $writer->insertOne($writerHeader);

    /* ------------------- */
    /* ---- GEOLOCATE ---- */
    /* ------------------- */

    $climate->info('Iniciando geolocalização dos dados');
    $geocoder = GeocoderServiceFactory::getInstance($climate);
    $linesProcessed = 0;
    $linesGeocoded = 0;
    foreach ($csv as $record) {
        $climate->info('Processando local com id: ' . $record['id']);
        $linesProcessed++;
        $address = new AddressDTO();
        $address->address = $record['endereco'];
        $address->uf = $record['uf'];;
        $address->cityName = $record['cidade'];;
        $address->postalCode = $record['cep'];
        $address->localName = $record['nome_local'];

        $newRecord = array_values($record);
        $geocoded = $geocoder->addressToGeocoded($address);

        if(is_null($geocoded)) {
            $climate->info('Geocodificação não realizada');
            $newRecord = array_merge($record, [
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ]);
        } else {
            $climate->info('Geocodificação realizada com sucesso com método: ' . $geocoded->strategyToGeocoded);
            $newRecord = array_merge($record, [
                $geocoded->type,
                $geocoded->location_type,
                $geocoded->formatted_address,
                $geocoded->lat,
                $geocoded->lng,
                $geocoded->city_long_name,
                $geocoded->district_long_name,
                $geocoded->strategyToGeocoded
            ]);
            $linesGeocoded++;
        }

        $writer->insertOne($newRecord);
    }

    /* ---------------- */
    /* ---- FINISH ---- */
    /* ---------------- */

    $climate->info('Processamento Finalizado');
    $climate->info('Linhas Processadas: ' . $linesProcessed);
    $climate->info('Linhas Geocodificadas: ' . $linesGeocoded);

} catch (CsvException $e) {
    $climate->error('Erro no processamento do CSV: ' . $e->getMessage());
    exit;
} catch (ClimateException $e) {
    $climate->error('Erro no uso do climate: ' . $e->getMessage());
    exit;
}