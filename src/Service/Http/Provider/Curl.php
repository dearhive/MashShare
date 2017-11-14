<?php

namespace Mashshare\Service\Http\Provider;

use Mashshare\Service\Http\Exception\HttpException;

/**
 * Class Curl
 * @package Mashshare\Service\Http\Provider
 */
class Curl implements ProviderInterface
{

    const REQUEST_TYPE_GET  = 'GET';

    const REQUEST_TYPE_POST = 'POST';

    /**
     * @var resource
     */
    private $handle;

    /**
     * @var string
     */
    private $response;

    public function __construct()
    {
        $this->handle = curl_init();

        $this->initOptions();
    }

    public function __destruct()
    {
        curl_close($this->handle);
    }

    public function __clone()
    {
        $request            = new self;
        $request->handle    = curl_copy_handle($this->handle);

        return $request;
    }

    /**
     * Default options
     */
    private function initOptions()
    {
        $this->setOptions(array(
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_AUTOREFERER     => true,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_MAXREDIRS       => 10,
            CURLOPT_HEADER          => true,
            CURLOPT_PROTOCOLS       => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_USERAGENT       => 'MASHSHARE 4.0.0 (Curl)',
            CURLOPT_CONNECTTIMEOUT  => 30,
            CURLOPT_TIMEOUT         => 30
        ));
    }

    /**
     * @param string $user
     * @param string $password
     *
     * @return null|string
     */
    private function getCredentials($user, $password)
    {
        if (empty($user) || !is_string($user))
        {
            return null;
        }

        $credentials = $user;

        if (empty($password) && is_string($password))
        {
            $credentials .= ':' . $password;
        }

        return $credentials;
    }

    /**
     * @return string
     * @throws HttpException
     */
    private function send()
    {
        $this->response = curl_exec($this->handle);

        if ($errorNo = curl_errno($this->handle))
        {
            throw new HttpException(curl_error($this->handle), $errorNo);
        }

        return $this->response;
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
     */
    protected function initPostFields($params = array())
    {
        if (empty($params))
        {
            return;
        }

        $this->setOption(CURLOPT_POSTFIELDS, $this->buildHttpQuery($params));
    }

    /**
     * @return bool
     */
    public static function isAvailable()
    {
        return extension_loaded('curl');
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        curl_setopt_array($this->handle, $options);

        return $this;
    }

    /**
     * @param int $option
     * @param mixed $value
     *
     * @return $this
     */
    public function setOption($option, $value)
    {
        curl_setopt($this->handle, $option, $value);

        return $this;
    }

    /**
     * @param int $timeout
     *
     * @return $this
     */
    public function setTimeout($timeout)
    {
        return $this->setOption(CURLOPT_TIMEOUT, $timeout);
    }

    /**
     * @param int $timeout
     *
     * @return Curl
     */
    public function setConnectTimeout($timeout)
    {
        return $this->setOption(CURLOPT_CONNECTTIMEOUT, $timeout);
    }

    /**
     * @param string $host
     * @param int $port
     * @param null|string $user
     * @param null|string $password
     *
     * @return $this
     */
    public function setProxy($host, $port = 8080, $user = null, $password = null)
    {
        $this->setOptions(array(
            CURLOPT_PROXY       => $host,
            CURLOPT_PROXYPORT   => $port,
        ));

        $credentials = $this->getCredentials($user, $password);

        if (null !== $credentials)
        {
            $this->setOption(CURLOPT_PROXYPASSWORD, $credentials);
        }

        return $this;
    }

    /**
     * @param string $uri
     * @param array $params
     *
     * @return string
     */
    public function get($uri, $params = array())
    {
        $this->setOptions(array(
            CURLOPT_URL             => $this->buildUri($uri, $params),
            CURLOPT_HTTPGET         => true,
            CURLOPT_CUSTOMREQUEST   => self::REQUEST_TYPE_GET,
        ));

        return $this->send();
    }

    /**
     * @param string $uri
     * @param array $params
     *
     * @return string
     */
    public function post($uri, $params = array())
    {
        $this->setOptions(array(
            CURLOPT_URL             => $uri,
            CURLOPT_POST            => true,
            CURLOPT_CUSTOMREQUEST   => self::REQUEST_TYPE_POST,
        ));

        $this->initPostFields($params);

        return $this->send();
    }
}