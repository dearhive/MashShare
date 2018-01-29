<?php

namespace Mashshare\Service\Network\Twitter;

use Mashshare\Service\Network\AbstractShareCountBase;

/**
 * Class ShareCount
 * @package Mashshare\Service\Network\Twitter
 */
class ShareCount extends AbstractShareCountBase
{

    const API_URI = 'http://public.newsharecounts.com/count.json';

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

        if (!isset($objShares->count))
        {
            return 'Twitter Share Count: No count in returned object';
        }

        return (int) $objShares->count;
    }
}