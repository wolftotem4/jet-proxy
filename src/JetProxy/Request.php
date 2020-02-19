<?php

namespace JetProxy;

use Closure;

class Request
{
    /**
     * @var resource
     */
    protected $curl;

    /**
     * @var \JetProxy\TransmissionHandler
     */
    protected $httpReceiver;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var string
     */
    protected $post = null;

    /**
     * Request constructor.
     * @param string   $method
     * @param resource $curl
     * @param \JetProxy\TransmissionHandler|null $handler
     */
    public function __construct($method, $curl, TransmissionHandler $handler = null)
    {
        $this->curl         = $curl;
        $this->httpReceiver = $handler ?: new CurlHttpReceiver();

        $this->httpReceiver->setCurl($curl);

        $this->setRequestMethod($method);
    }

    /**
     * @param  string  $method
     * @param  string  $url
     * @param  \JetProxy\TransmissionHandler|null  $handler
     * @return static
     */
    public static function make($method, $url, $handler = null)
    {
        return new static($method, curl_init($url), $handler);
    }

    /**
     * @param  \Closure  $closure
     * @return $this
     */
    public function setupCurl(Closure $closure)
    {
        $closure($this->curl);
        return $this;
    }

    /**
     * @return \JetProxy\TransmissionHandler
     */
    public function getTransmissionHandler()
    {
        return $this->httpReceiver;
    }

    /**
     * @return \JetProxy\TransmissionHandler
     *
     * @deprecated
     */
    public function getHttpReceiver()
    {
        return $this->getTransmissionHandler();
    }

    /**
     * @return string|null
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param  array|string|null  $post
     * @return $this
     */
    public function setPost($post)
    {
        $this->post = is_array($post) ? http_build_query($post, '', '&') : $post;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param  array  $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @param  string  $header
     * @return $this
     */
    public function addHeader($header)
    {
        $this->headers[] = $header;
        return $this;
    }

    /**
     * @return $this
     *
     * @throws \JetProxy\ClientRequestErrorException
     */
    public function run()
    {
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);
        if (! $this->emptyBody($this->post)) {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->post);
        }

        curl_exec($this->curl);

        if ($errno = curl_errno($this->curl)) {
            throw new ClientRequestErrorException(curl_error($this->curl), $errno);
        }

        $this->httpReceiver->finish($this->curl);

        curl_close($this->curl);

        return $this;
    }

    /**
     * @param  mixed  $body
     * @return bool
     */
    protected function emptyBody($body)
    {
        return $body === null || $body === false || $body === '';
    }

    /**
     * @param string    $method
     */
    private function setRequestMethod($method)
    {
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($this->curl, CURLOPT_POST, true);
                break;
            case 'HEAD':
                curl_setopt($this->curl, CURLOPT_NOBODY, true);
                break;
            case 'PUT':
                curl_setopt($this->curl, CURLOPT_PUT, true);
                break;
            case 'OPTIONS':
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
                curl_setopt($this->curl, CURLOPT_NOBODY, true);
                break;
        }
    }
}