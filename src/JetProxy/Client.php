<?php

namespace JetProxy;

class Client implements ClientInterface
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $ipAddress;

    /**
     * @var string
     */
    protected $protocol;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var \JetProxy\TransmissionHandler|null
     */
    protected $transmissionHandler = null;

    /**
     * Client constructor.
     * @param $host
     * @param string|null  $ipAddress
     * @param string       $protocol
     * @param int|null     $port
     */
    public function __construct($host, $ipAddress = null, $protocol = 'http', $port = null)
    {
        $this->host      = $host;
        $this->ipAddress = $ipAddress ?: gethostbyname($this->host);
        $this->protocol  = $protocol;
        $this->port      = $port ?: $this->defaultPort();
    }

    /**
     * @param  \JetProxy\TransmissionHandler  $transmissionHandler
     * @return $this
     */
    public function setTransmissionHandler(TransmissionHandler $transmissionHandler)
    {
        $this->transmissionHandler = $transmissionHandler;
        return $this;
    }

    /**
     * @param  string  $method
     * @param  string  $uri
     * @return \JetProxy\Request
     */
    public function request($method, $uri)
    {
        $baseUri = $this->buildBaseUri($uri);

        $request = Request::make($method, $baseUri, $this->transmissionHandler);

        $request->addHeader('Host: ' . $this->host);

        return $request;
    }

    /**
     * @return int
     */
    protected function defaultPort()
    {
        return ($this->protocol == 'https') ? 443 : 80;
    }

    /**
     * @param  string  $uri
     * @return string
     */
    protected function buildBaseUri($uri)
    {
        $uri = '/' . ltrim($uri, '/');
        $port = ($this->port == $this->defaultPort()) ? '' : ':' . $this->port;

        return $this->protocol . '://' . $this->ipAddress . $port . $uri;
    }
}