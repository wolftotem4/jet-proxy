<?php

namespace JetProxy;

class BrowserRequestProxy implements ClientInterface
{
    /**
     * @var \JetProxy\ClientInterface
     */
    protected $client;

    /**
     * BrowserRequestProxy constructor.
     * @param \JetProxy\ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param  string  $method
     * @param  string  $uri
     * @return \JetProxy\Request
     */
    public function request($method, $uri)
    {
        $request = $this->client->request($method, $uri);

        (! empty($_POST)) and $request->setPost($_POST);

        foreach (getallheaders() as $key => $value) {
            if (! in_array(strtolower($key), ['connection', 'accept-encoding', 'host'])) {
                $request->addHeader($key . ': ' . $value);
            }
        }

        return $request;
    }

    /**
     * @return string
     */
    private function cookieString()
    {
        return http_build_query($_COOKIE, '', '; ', PHP_QUERY_RFC3986);
    }
}