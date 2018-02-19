<?php

namespace Mashshare\Service\Http\Provider\Curl;

use Mashshare\Service\Http\Provider\ResponseInterface;

/**
 * Class Response
 * @package Mashshare\Service\Http\Provider\Curl
 */
class Response implements ResponseInterface
{

    /**
     * @var string
     */
    private $response;

    /**
     * @var resource
     */
    private $handle;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var string
     */
    private $body;

    /**
     * @var int
     */
    private $status;

    public function __construct($handle, $response)
    {
        $this->handle   = $handle;
        $this->response = $response;

        $this->handleResponse();
    }

    private function handleResponse()
    {
        $headerSize     = curl_getinfo($this->handle, CURLINFO_HEADER_SIZE);

        $this->status   = curl_getinfo($this->handle, CURLINFO_HTTP_CODE);
        $this->headers  = $this->getHeadersAsArray($headerSize);
        $this->body     = substr($this->response, $headerSize);
    }

    /**
     * @param int $headerSize
     *
     * @return array|null
     */
    private function getHeadersAsArray($headerSize)
    {
        $headersText  = substr($this->response, 0, $headerSize);

        if (!$headersText)
        {
            return null;
        }

        $headers = array();

        foreach (explode("\r\n", $headersText) as $key => $line)
        {
            list ($key, $value) = explode(': ', $line);

            if (!$key || !$value)
            {
                continue;
            }

            $headers[$key] = $value;
        }

        return $headers;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return mixed
     */
    public function getJsonResponse()
    {
        return json_decode($this->getBody());
    }
}