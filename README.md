# php-google-geocoding
Repositório com script PHP para geolocalizar um conjunto de endereços utilizando a "Geocoding API" do google.

O objetivo é a criação de um script o mais direto possível para a partir de uma planilha de endereços, buscar a geolocalização tendo o CEP como filtro principal.

A api usada nas requests é a api do Google Geocoding: https://developers.google.com/maps/documentation/geocoding/start

## Requisitos

* PHP 7.2 ou superior;
* Arquivo CSV em UTF-8
´
## Exemplo de Uso

Para exemplo do modelo de arquivo sendo utilizado, veja a pasta "sample":

```shell script
php geocode.php sample/sample.csv
``` 
