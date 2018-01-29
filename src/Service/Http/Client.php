<?php

namespace Mashshare\Service\Http;
use Mashshare\Service\Http\Exception\ProviderException;
use Mashshare\Service\Http\Provider\Curl\Curl;

/**
 * Class Client
 * @package Mashshare\Service\Http
 */
class Client
{

    const VERSION = '1.0.0';

    public static function getProvider()
    {
        if (Curl::isAvailable())
        {
            return new Curl();
        }

        throw new ProviderException('There isn\'t any available provider');
    }
}