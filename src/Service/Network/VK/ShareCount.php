<?php

namespace Mashshare\Service\Network\VK;

use Mashshare\Service\Network\AbstractShareCountBase;

/**
 * Class ShareCount
 * @package Mashshare\Service\Network\VK
 */
class ShareCount extends AbstractShareCountBase
{

    const API_URI = 'http://public.newsharecounts.com/count.json';

    /**
     * @param string $response
     *
     * @return int|null
     */
    private function parseResponse($response)
    {
        $data = preg_match('/^VK.Share.count\(\d+,\s+(\d+)\);$/i', $response, $matches);

        if (!isset($matches[1]))
        {
            return null;
        }

        return (int) $matches[1];
    }

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
            return 'VK Share Count: No count in returned object';
        }

        return (int) $objShares->count;
    }
}