<?php

namespace Konscia\GoogleGeocoding;

class GeocodingException extends \LogicException
{
    public function __construct(string $status, ?string $message)
    {
        parent::__construct("Erro de geolocalização na api do google. Status: {$status}. Mensagem: {$message}");
    }
}