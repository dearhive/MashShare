<?php

namespace Mashshare\Service\Http\Provider;

/**
 * Interface ProviderInterface
 * @package Mashshare\Service\Http\Provider
 */
interface ProviderInterface
{
    /**
     * @param string $uri
     * @param array $params
     *
     * @return string
     */
    public function get($uri, $params = array());

    /**
     * @param string $uri
     * @param array $params
     *
     * @return string
     */
    public function post($uri, $params = array());
}