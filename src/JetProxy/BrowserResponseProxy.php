<?php

namespace JetProxy;

class BrowserResponseProxy implements ClientInterface
{
    /**
     * @var \JetProxy\ClientInterface
     */
    protected $client;

    /**
     * @var int|false
     */
    protected $contentLength = false;

    /**
     * @var bool
     */
    protected $useChunkedTransfer = false;

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
        $request->getTransmissionHandler()->addHeaderListener(function ($httpCode, $header) {
            $this->receiveHeader($httpCode, $header);
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
    protected function receiveHeader($httpCode, $header)
    {
        if (preg_match('#(*BSR_ANYCRLF)^HTTP/\S+\s.*\R?#', $header, $match)) {
            $header = substr($header, strlen($match[0]));
        }
        $headers = HttpHeaderParser::parse($header);

        $this->contentLength      = $this->contentLength($headers);
        $this->useChunkedTransfer = (! $this->contentLength);

        http_response_code($httpCode);
        $this->transferHeaders($headers);
        if ($this->useChunkedTransfer) {
            $this->startChunk();
        }
    }

    /**
     * @param string  $data
     */
    protected function receiveBody($data)
    {
        if ($this->useChunkedTransfer) {
            echo dechex(strlen($data)), "\r\n", $data, "\r\n";
            flush();
        } else {
            echo $data;
        }
    }

    protected function startChunk()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Transfer-Encoding: chunked');
    }

    protected function endChunk()
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

    /**
     * @param  array  $headers
     * @return int|false
     */
    private function contentLength(array $headers)
    {
        foreach ($headers as $header) {
            if (strtolower($header['key']) == 'content-length') {
                return intval($header['value']);
            }
        }
        return false;
    }
}