<?php

namespace Mashshare\Service\Network;
use Mashshare\Service\Http\Client;
use Mashshare\Service\Http\Exception\ProviderException;
use Mashshare\Service\Network\Interfaces\InterfaceShareCount;

/**
 * Class ShareCount
 * @package Mashshare\Service\Network
 */
class ShareCount
{

    /**
     * @var array
     */
    private $networks = array(
        'Facebook',
        'Twitter',
        'GooglePlus',
        'LinkedIn',
        'Pinterest',
        'StumbleUpon',
        'VK',
    );

    /**
     * @var int
     */
    private $postId;

    /**
     * @var string
     */
    private $facebookCountType;

    /**
     * @var string
     */
    private $facebookAccessToken;

    /**
     * @var array
     */
    private $shares = array();

    /**
     * @var array
     */
    private $errors = array();

    public function __construct()
    {

        $settings = get_option('mashsb_settings');

        if (isset($settings['facebook_count_mode']))
        {
            $this->setFacebookCountType($settings['facebook_count_mode']);
        }

        if (isset($settings['fb_access_token']) && !empty($settings['fb_access_token']))
        {
            $this->setFacebookAccessToken($settings['fb_access_token']);
        }
    }

    /**
     * @param InterfaceShareCount $service
     * @param string $network
     *
     * @return string
     */
    private function getNetworkDataName($service, $network)
    {
        if ($service instanceof \Mashshare\Service\Network\Facebook\ShareCount)
        {
            return $network . '_' . $service->getType();
        }

        if ($service instanceof \Mashshare\Service\Network\GooglePlus\ShareCount)
        {
            return 'Google';
        }

        return $network;
    }

    /**
     * @return int
     */
    public function getPostId()
    {
        return (int) $this->postId;
    }

    /**
     * @param int $postId
     *
     * @return $this
     */
    public function setPostId($postId)
    {
        $this->postId = (int) $postId;

        return $this;
    }

    /**
     * @return string
     */
    public function getFacebookCountType()
    {
        return $this->facebookCountType;
    }

    /**
     * @param string $facebookCountType
     *
     * @return $this
     */
    public function setFacebookCountType($facebookCountType)
    {
        $this->facebookCountType = $facebookCountType;

        return $this;
    }

    /**
     * @return string
     */
    public function getFacebookAccessToken()
    {
        return $this->facebookAccessToken;
    }

    /**
     * @param string $facebookAccessToken
     *
     * @return $this
     */
    public function setFacebookAccessToken($facebookAccessToken)
    {
        $this->facebookAccessToken = $facebookAccessToken;

        return $this;
    }

    /**
     * @return array
     */
    public function getNetworks()
    {
        return $this->networks;
    }

    /**
     * @return array
     */
    public function getShares()
    {
        return $this->shares;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return $this
     */
    public function requestShares()
    {
        $url = get_permalink($this->getPostId());

        if (!$url)
        {
            return $this;
        }

        try
        {
            $client = Client::getProvider();
        }
        catch (ProviderException $e)
        {
            return $this;
        }

        $totalShares = 0;

        foreach ($this->networks as $network)
        {
            $class = '\\Mashshare\\Service\\Network\\' . $network . '\\ShareCount';

            /** @var InterfaceShareCount $service */
            $service = new $class;
            $service->setClient($client);
            $service->setUrl($url);

            if ($service instanceof \Mashshare\Service\Network\Facebook\ShareCount)
            {
                $service->setType($this->getFacebookCountType());
                $service->setToken($this->getFacebookAccessToken() ? :null);
            }

            $service->sendRequest();

            $response = $service->getShares();

            $networkName = $this->getNetworkDataName($service, $network);

            if (is_int($response))
            {
                $totalShares += $response;

                $this->shares[$networkName] = $response;
            }
            else
            {
                $this->shares[$networkName] = 0;
                $this->errors[$networkName] = $response;
            }
        }

        $this->shares['total'] = $totalShares;

        return $this;
    }
}