<?php

use League\CLImate\CLImate;
use League\CLImate\Exceptions\Exception as ClimateException;
use League\Csv\Exception as CsvException;
use League\Csv\Reader;

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
    "bairro",
    "enedereco",
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

    $csv = Reader::createFromPath($path, 'r');
    $csv->setDelimiter(CSV_DELIMITER);
    $header = $csv->fetchOne(0);

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

    $climate->info('TO-DO: criação do arquivo csv de saída');

    /* ------------------- */
    /* ---- GEOLOCATE ---- */
    /* ------------------- */

    $climate->info('TO-DO: geolocalização dos dados');

    /* ---------------- */
    /* ---- FINISH ---- */
    /* ---------------- */

    $climate->info('TO-DO: estatísticas e mensagens finais');

} catch (CsvException $e) {
    $climate->error('Erro no processamento do CSV: ' . $e->getMessage());
    exit;
} catch (ClimateException $e) {
    $climate->error('Erro no uso do climate: ' . $e->getMessage());
    exit;
}