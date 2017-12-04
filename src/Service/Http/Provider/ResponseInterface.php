<?php

namespace Mashshare\Service\Http\Provider;

/**
 * Interface ResponseInterface
 * @package Mashshare\Service\Http\Provider
 */
interface ResponseInterface
{

    /**
     * @return string
     */
    public function getBody();

    /**
     * @return array
     */
    public function getHeaders();

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @return string
     */
    public function getResponse();

    /**
     * @return mixed
     */
    public function getJsonResponse();
}