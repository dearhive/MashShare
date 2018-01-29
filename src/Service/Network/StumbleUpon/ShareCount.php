<?php

namespace Mashshare\Service\Network\StumbleUpon;

use Mashshare\Service\Network\AbstractShareCountBase;

/**
 * Class ShareCount
 * @package Mashshare\Service\Network\StumbleUpon
 */
class ShareCount extends AbstractShareCountBase
{

    const API_URI = 'http://www.stumbleupon.com/services/1.01/badge.getinfo';

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
        $objShares = $this->getResponse()
            ->getJsonResponse()
        ;

        if (!isset($objShares->result) || !isset($objShares->result->views))
        {
            return 'Twitter Share Count: No count in returned object';
        }

        return (int) $objShares->result->views;
    }
}