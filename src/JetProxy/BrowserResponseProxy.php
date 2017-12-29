<?php

namespace JetProxy;

class BrowserResponseProxy implements ClientInterface
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
        $request->getHttpReceiver()->addHeaderListener(function ($httpCode, $header) {
            $this->receiveHeader($httpCode, $header);
            $this->startChunk();
        })->addBufferListener(function ($data) {
            $this->receiveBody($data);
        })->addEndListener(function () {
            $this->endChunk();
        });
        return $request;
    }

    /**
     * @param int     $httpCode
     * @param string  $header
     */
    public function receiveHeader($httpCode, $header)
    {
        if (preg_match('#(*BSR_ANYCRLF)^HTTP/\S+\s.*\R?#', $header, $match)) {
            $header = substr($header, strlen($match[0]));
        }
        $headers = HttpHeaderParser::parse($header);

        http_response_code($httpCode);
        $this->transferHeaders($headers);
    }

    /**
     * @param string  $data
     */
    public function receiveBody($data)
    {
        echo dechex(strlen($data)), "\r\n", $data, "\r\n";
        flush();
    }

    public function startChunk()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Transfer-Encoding: chunked');
    }

    public function endChunk()
    {
        echo "0\r\n\r\n";
    }

    /**
     * @param array $headers
     */
    protected function transferHeaders(array $headers)
    {
        $blacklist = ['server', 'connection', 'transfer-encoding', 'x-powered-by'];

        $sentKeys = [];
        foreach ($headers as $header) {
            $key = strtolower($header['key']);
            $sending = $header['key'] . ': ' . $header['value'];

            if (in_array($key, $blacklist)) {
                continue;
            } elseif (in_array($key, $sentKeys)) {
                header($sending, false);
            } else {
                header($sending);
                $sentKeys[] = $key;
            }
        }
    }
}