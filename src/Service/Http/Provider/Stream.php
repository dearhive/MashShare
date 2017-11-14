<?php

namespace Mashshare\Service\Http\Provider;

use Mashshare\Service\Http\Exception\HttpException;

/**
 * Class Stream
 * @package Mashshare\Service\Http\Provider
 */
class Stream implements ProviderInterface
{

    const REQUEST_TYPE_GET  = 'GET';

    const REQUEST_TYPE_POST = 'POST';

    const SCHEMA_HTTP       = 'HTTP';

    const SCHEMA_HTTPS      = 'HTTPS';

    /**
     * @var resource
     */
    private $context;

    /**
     * @var string
     */
    private $response;

    public function __construct()
    {
        $this->context = stream_context_create();
        $this->initOptions();
    }

    public function __destruct()
    {
        $this->context = null;
    }

    private function initOptions()
    {
        $this->setOptions(array(
            'user_agent'      => 'MASHSHARE 4.0.0 (Stream)',
            'follow_location' => 1,
            'max_redirects'   => 20,
            'timeout'         => 30
        ));
    }

    /**
     * @param int $errorNo
     * @param string $errorMessage
     *
     * @throws HttpException
     */
    private function errorHandler($errorNo, $errorMessage)
    {
        throw new HttpException($errorMessage, $errorNo);
    }

    /**
     * @param string $uri
     *
     * @return bool|string
     */
    private function send($uri)
    {
        set_error_handler(array($this, 'errorHandler'));

        $content = file_get_contents($uri, false, $this->context);

        restore_error_handler();

        $this->response = $content;

        return $this->response;
    }

    /**
     * @param array $params
     *
     */
    private function initPostFields(array $params)
    {
        if (empty($params) || !is_array($params))
        {
            return;
        }

        $this->setOption('content', $this->buildHttpQuery($params));
    }

    /**
     * @param string $uri
     * @param array $params
     *
     * @return string
     */
    protected function buildUri($uri, $params = array())
    {
        if (empty($params))
        {
            return $uri;
        }

        return $uri . '?' . $this->buildHttpQuery($params);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    protected function buildHttpQuery(array $params)
    {
        return http_build_query($params);
    }

    /**
     * @return bool
     */
    public static function isAvailable()
    {
        $wrappers = stream_get_wrappers();

        return in_array(self::SCHEMA_HTTP, $wrappers) && in_array(self::SCHEMA_HTTPS, $wrappers);
    }

    /**
     * @param string $option
     * @param string $value
     * @param string $schema
     *
     * @return $this
     */
    public function setOption($option, $value, $schema = self::SCHEMA_HTTP)
    {
        stream_context_set_option($this->context, $schema, $option, $value);

        return $this;
    }

    /**
     * @param array $options
     * @param string $schema
     *
     * @return $this
     */
    public function setOptions($options, $schema = self::SCHEMA_HTTP)
    {
        stream_context_set_option($this->context, array($schema => $options));

        return $this;
    }

    /**
     * @param int $timeout
     *
     */
    public function setTimeout($timeout)
    {
        $this->setOption('timeout', $timeout);
    }

    /**
     * @param string $uri
     * @param array $params
     *
     * @return bool|string
     */
    public function get($uri, $params = array())
    {

        $this->setOptions(array(
            'method'  => self::REQUEST_TYPE_GET,
            'content' => '',
        ));

        return $this->send($this->buildUri($uri, $params));
    }

    /**
     * @param string $uri
     * @param array $params
     *
     * @return bool|string
     */
    public function post($uri, $params = array())
    {
        $this->setOption('method', self::REQUEST_TYPE_POST);

        $this->initPostFields($params);

        return $this->send($uri);
    }
}