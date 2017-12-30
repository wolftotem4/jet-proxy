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
     * @var \JetProxy\CurlHttpReceiver
     */
    protected $httpReceiver;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var array
     */
    protected $post = [];

    /**
     * Request constructor.
     * @param string   $method
     * @param resource $curl
     */
    public function __construct($method, $curl)
    {
        $this->curl         = $curl;
        $this->httpReceiver = (new CurlHttpReceiver())->setCurl($curl);

        $this->setRequestMethod($method);
    }

    /**
     * @param  string  $method
     * @param  string  $url
     * @return static
     */
    public static function make($method, $url)
    {
        return new static($method, curl_init($url));
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
     * @return \JetProxy\CurlHttpReceiver
     */
    public function getHttpReceiver()
    {
        return $this->httpReceiver;
    }

    /**
     * @param  array  $post
     * @return $this
     */
    public function setPost(array $post)
    {
        $this->post = $post;
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
        if (! empty($this->post)) {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($this->post, '', '&'));
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