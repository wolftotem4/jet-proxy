<?php

namespace JetProxy;

class Client
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

    public function request($uri)
    {
        $baseUri = $this->buildBaseUri($uri);

        $httpReceiver = new CurlHttpReceiver();
        $httpReceiver->addHeaderListener(function ($header) {
            if (preg_match('#(*BSR_ANYCRLF)^HTTP/\S+\s.*\R?#', $header, $match)) {
                $header = substr($header, strlen($match[0]));
            }
            $headers = HttpHeaderParser::parse($header);

//            header('Content-Type: text/plain; charset=utf-8');
//            print_r($headers);
            $this->transferHeaders($headers);
        })->addBufferListener(function ($data) {
            echo $data;
        });

        $ch = curl_init($baseUri);

        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Host: ' . $this->host]);
        $httpReceiver->setCurl($ch);

        curl_exec($ch);


        curl_close($ch);
    }

    protected function defaultPort()
    {
        return ($this->protocol == 'https') ? 443 : 80;
    }

    protected function buildBaseUri($uri)
    {
        $uri = '/' . ltrim($uri, '/');
        $port = ($this->port == $this->defaultPort()) ? '' : ':' . $this->port;

        return $this->protocol . '://' . $this->host . $port . $uri;
    }

    /**
     * @param array $headers
     */
    public function transferHeaders(array $headers)
    {
        $blacklist = ['server', 'connection', 'transfer-encoding'];

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