<?php

namespace Mashshare\Service\Http;
use Mashshare\Service\Http\Exception\ProviderException;
use Mashshare\Service\Http\Provider\Curl;
use Mashshare\Service\Http\Provider\Stream;

/**
 * Class Client
 * @package Mashshare\Service\Http
 */
class Client
{

    const VERSION = '1.0.0';

    public static function getProvider()
    {
        if (Curl::isAvailable()) {
            return new Curl();
        }

        if (Stream::isAvailable()) {
            return new Stream();
        }

        throw new ProviderException('There isn\'t any available provider');
    }
}