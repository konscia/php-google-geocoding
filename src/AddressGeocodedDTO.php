<?php

namespace Konscia\GoogleGeocoding;

class AddressGeocodedDTO
{
    public $type;
    public $place_id;
    public $partial_match;
    public $location_type;
    public $formatted_address;
    public $lat;
    public $lng;
    public $city_long_name;
    public $district_long_name;
}