<?php

namespace Mashshare\Service\Network\Interfaces;

use Mashshare\Service\Http\Provider\ProviderInterface;
use Mashshare\Service\Http\Provider\ResponseInterface;

/**
 * Interface InterfaceShareCount
 * @package Mashshare\Service\Network\Interfaces
 */
interface InterfaceShareCount
{

    /**
     * @param ProviderInterface $client
     *
     * @return $this
     */
    public function setClient($client);

    /**
     * @return ProviderInterface
     */
    public function getClient();

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url);

    /**
     * @return ResponseInterface
     */
    public function getResponse();

    /**
     * @return $this
     */
    public function sendRequest();

    /**
     * @return int|string
     */
    public function getShares();
}