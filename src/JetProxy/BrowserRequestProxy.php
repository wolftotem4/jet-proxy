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
        (! empty($_COOKIE)) and $request->addHeader('Cookie: ' . $this->cookieString());

        $headerKeys = [
            'HTTP_ACCEPT'           => 'Accept',
            'HTTP_ACCEPT_CHARSET'   => 'Accept-Charset',
            'HTTP_ACCEPT_LANGUAGE'  => 'Accept-Language',
            'HTTP_CACHE_CONTROL'    => 'Cache-Control',
            'HTTP_IF_NONE_MATCH'    => 'If-None-Match',
            'HTTP_PRAGMA'           => 'Pragma',
            'HTTP_REFERER'          => 'Referer',
            'HTTP_USER_AGENT'       => 'User-Agent',
            'HTTP_X_REQUESTED_WITH' => 'X-Requested-With',
        ];
        foreach ($headerKeys as $sKey => $key) {
            if (! empty($_SERVER[$sKey])) {
                $request->addHeader($key . ': ' . $_SERVER[$sKey]);
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