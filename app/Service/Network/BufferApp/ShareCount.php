<?php

namespace Mashshare\Service\Network\BufferApp;

use Mashshare\Service\Network\AbstractShareCountBase;

/**
 * Class ShareCount
 * @package Mashshare\Service\Network\BufferApp
 */
class ShareCount extends AbstractShareCountBase
{

    const API_URL = 'https://api.bufferapp.com/1/links/shares.json';

    /**
     * @param string $responseBody
     *
     * @return int
     */
    private function parseResponseBody($responseBody)
    {
        preg_match("#window\.__SSR = {c: ([\d]+)#", $obj->response->body, $matches);

        if (!isset($matches[0]))
        {
            return 0;
        }

        $shares = (int) str_replace("window.__SSR = {c: ", '', $matches[0]);

        return $shares;
    }

    /**
     * @return string
     */
    protected function getApiUri()
    {
        return self::API_URL;
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

        if (!isset($objShares->shares))
        {
            return 'BufferApp Share Count: Invalid response';
        }

        $shares = (int) $objShares->shares;

        return $shares;
    }
}