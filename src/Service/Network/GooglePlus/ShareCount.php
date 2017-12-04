<?php

namespace Mashshare\Service\Network\GooglePlus;

use Mashshare\Service\Network\AbstractShareCountBase;

/**
 * Class ShareCount
 * @package Mashshare\Service\Network\GooglePlus
 */
class ShareCount extends AbstractShareCountBase
{

    const API_URL = 'https://plusone.google.com/_/+1/fastbutton';

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
        $objShares = $this->getResponse();

        if (!$objShares->getBody())
        {
            return 'GooglePlus Share Count: Invalid response';
        }

        $shares = $this->parseResponseBody($objShares->getBody());

        return $shares;
    }
}