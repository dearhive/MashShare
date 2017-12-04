<?php

namespace Mashshare\Service\Network\LinkedIn;

use Mashshare\Service\Network\AbstractShareCountBase;

/**
 * Class ShareCount
 * @package Mashshare\Service\Network\Linkedin
 */
class ShareCount extends AbstractShareCountBase
{

    const API_URI = 'https://www.linkedin.com/countserv/count/share';

    const API_OPTION_FORMAT_JSON = 'json';

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
            'format'    => self::API_OPTION_FORMAT_JSON,
            'url'       => $this->getUrl(),
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
            return 'LinkedIn Share Count: No count in returned object';
        }

        return (int) $objShares->count;
    }
}