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
     * @param  string  $method
     * @param  string  $uri
     * @return \JetProxy\Request
     */
    public function request($method, $uri)
    {
        $baseUri = $this->buildBaseUri($uri);

        $request = Request::make($method, $baseUri);

        return $request;
    }

    /**
     * @return array
     */
    protected function requestHeaders()
    {
        $headers[] = 'Host: ' . $this->host;
        if (! empty($_COOKIE)) {
            $headers[] = 'Cookie: ' . http_build_query($_COOKIE, '', '; ', PHP_QUERY_RFC3986);
        }
        return $headers;
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

        return $this->protocol . '://' . $this->host . $port . $uri;
    }
}