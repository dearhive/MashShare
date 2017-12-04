<?php

namespace Mashshare\Service\Network;

use Mashshare\Service\Http\Provider\ProviderInterface;
use Mashshare\Service\Http\Provider\ResponseInterface;
use Mashshare\Service\Network\Interfaces\InterfaceShareCount;

abstract class AbstractShareCountBase implements InterfaceShareCount
{

    /**
     * @var ProviderInterface
     */
    protected $client;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @return string
     */
    abstract protected function getApiUri();

    /**
     * @return array
     */
    abstract protected function getOptions();

    /**
     * @return int|string
     */
    abstract public function getShares();

    /**
     * @param ProviderInterface $client
     *
     * @return $this
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return ProviderInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return $this
     */
    public function sendRequest()
    {
        $this->response = $this->getClient()
                               ->get($this->getApiUri(), $this->getOptions())
        ;

        return $this;
    }
}