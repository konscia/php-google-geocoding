# php-google-geocoding
Repositório com script PHP para geolocalizar um conjunto de endereços utilizando a "Geocoding API" do google.

O objetivo é a criação de um script o mais direto possível para a partir de uma planilha de endereços, buscar a geolocalização tendo o CEP como filtro principal.

A api usada nas requests é a api do Google Geocoding: https://developers.google.com/maps/documentation/geocoding/start

## Requisitos

* PHP 7.2 ou superior;
* Arquivo CSV em UTF-8
* php.ini ou ini_set "allow_url_fopen = 1";
´
## Exemplo de Uso

Para exemplo do modelo de arquivo sendo utilizado, veja a pasta "sample":

```shell script
php geocode.php sample/sample.csv
``` 

Você também pode utilizar o exemplo de apenas uma linha para não consumir seus créditos de api:

```shell script
php geocode.php sample/sample-one-line.csv
```

Um segundo parâmetro pode ser passado para informar o nome do arquivo de saída na pasta 'output'.

```shell script
php geocode.php sample/sample-one-line.csv one-result
``` 

O arquivo test_geocoding_api pode ser utilizado para testes individuais de geolocalização

```shell script
/php-google-geocoding/tests$ php test_geocoding_api.php
```