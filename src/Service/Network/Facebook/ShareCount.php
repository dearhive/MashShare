<?php

namespace Mashshare\Service\Network\Facebook;

use Mashshare\Service\Http\Provider\ProviderInterface;

/**
 * Class ShareCount
 * @package Mashshare\Service\Network\Facebook
 */
class ShareCount
{

    const API_URI = 'http://graph.facebook.com/';

    const API_OAUTH_URI = 'https://graph.facebook.com/v2.7/';

    const TYPE_SHARES = 'shares';

    const TYPE_LIKES = 'likes';

    const TYPE_BOTH = 'both';

    const REQUEST_TYPE = 'GET';

    /**
     * @var ProviderInterface
     */
    private $client;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $type;

    private $response;

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
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiUri()
    {
        return $this->getToken() ? self::API_OAUTH_URI : self::API_URI;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return $this
     */
    public function sendRequest()
    {
        $options = array(
            'id' => $this->getUrl(),
        );

        if ($this->getToken())
        {
            $options['access_token'] = $this->getToken();
        }

        $this->response = $this->getClient()
            ->get($this->getApiUri(), $options)
        ;

        return $this;
    }
}