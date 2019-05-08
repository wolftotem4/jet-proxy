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

        $this->setRequestPost($request);

        $this->setRequestHeaders($request);

        return $request;
    }

    /**
     * @return array|string
     */
    protected function getPostFromClient()
    {
        if (! empty($_POST)) {
            return $_POST;
        } else {
            return file_get_contents('php://input');
        }
    }

    /**
     * @param \JetProxy\Request  $request
     */
    protected function setRequestPost(Request $request)
    {
        $request->setPost($this->getPostFromClient());
    }

    /**
     * @param \JetProxy\Request $request
     */
    protected function setRequestHeaders(Request $request)
    {
        foreach (getallheaders() as $key => $value) {
            if (! in_array(strtolower($key), ['connection', 'accept-encoding', 'host'])) {
                $request->addHeader($key . ': ' . $value);
            }
        }
    }
}