<?php

namespace Mashshare\Service\Network\Facebook;

use Mashshare\Service\Network\AbstractShareCountBase;

/**
 * Class ShareCount
 * @package Mashshare\Service\Network\Facebook
 */
class ShareCount extends AbstractShareCountBase
{

    const API_URI = 'http://graph.facebook.com/';

    const API_OAUTH_URI = 'https://graph.facebook.com/v2.7/';

    const TYPE_SHARES = 'shares';

    const TYPE_COMMENTS = 'comments';

    const TYPE_TOTAL = 'total';

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $type;

    /**
     * @return string
     */
    protected function getApiUri()
    {
        //return $this->getToken() ? self::API_OAUTH_URI : self::API_URI;
        return self::API_URI;
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        $options = array(
            'id' => $this->getUrl(),
        );

//        if ($this->getToken())
//        {
//            $options['access_token'] = $this->getToken();
//        }

        return $options;
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
     * @return int|string
     */
    public function getShares()
    {
        $objShares = $this->getResponse()
                          ->getJsonResponse()
        ;

        if (isset($objShares->error))
        {
            return $objShares->error->message;
        }

        $shares = 0;

        if (!isset($objShares->share->comment_count) || !isset($objShares->share->share_count))
        {
            return $shares;
        }

        switch($this->getType())
        {
            case self::TYPE_COMMENTS:
                $shares = (int) $objShares->share->comment_count;
                break;
            case self::TYPE_SHARES:
                $shares = (int) $objShares->share->share_count;
                break;
            case self::TYPE_TOTAL:
            default:
                $shares = (int) $objShares->share->share_count + (int) $objShares->share->comment_count;
        }

        return $shares;
    }
}