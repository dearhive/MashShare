<?php

namespace Mashshare\Service\Network\Pinterest;

use Mashshare\Service\Network\AbstractShareCountBase;

/**
 * Class ShareCount
 * @package Mashshare\Service\Network\Pinterest
 */
class ShareCount extends AbstractShareCountBase
{

    const API_URI = 'http://api.pinterest.com/v1/urls/count.json';

    /**
     * @return string
     */
    protected function getApiUri()
    {
        return self::API_URI;
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        return array(
            'url' => $this->getUrl(),
        );
    }

    /**
     * @return int|string
     */
    public function getShares()
    {
        $objShares = $this->getResponse();

        if (!$objShares->getBody())
        {
            return 'Pinterest Share Count: Invalid response';
        }

        $jsonString = substr($objShares->getBody(), 13, -1);

        $responseJson = json_decode($jsonString);

        if (!isset($responseJson->count))
        {
            return 'Pinterest Share Count: Decoded JSON string doesn\'t include count';
        }

        $shares = (int) $responseJson->count;

        return $shares;
    }
}