<?php

namespace JetProxy;

use Closure;

class CurlHttpReceiver implements TransmissionHandler
{
    /**
     * @var string
     */
    protected $bufferedHeader = '';

    /**
     * @var array
     */
    protected $headerListeners = [];

    /**
     * @var array
     */
    protected $contentBufferListeners = [];

    /**
     * @var array
     */
    protected $endTransmissionListeners = [];

    /**
     * @var bool
     */
    protected $headerEOL = false;

    /**
     * @param  resource  $ch
     * @return $this
     */
    public function setCurl($ch)
    {
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, [$this, 'headerFunc']);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, [$this, 'contentFunc']);
        return $this;
    }

    /**
     * @param  resource  $ch
     * @param  string    $data
     * @return int
     */
    public function headerFunc($ch, $data)
    {
        $this->bufferedHeader .= $data;
        return strlen($data);
    }

    /**
     * @param  resource  $ch
     * @param  string    $data
     * @return int
     */
    public function contentFunc($ch, $data)
    {
        if (! $this->headerEOL) {
            $this->headerEOL = true;
            $this->headerReceived($ch);
        }
        $this->triggerBufferListener($data);
        return strlen($data);
    }

    /**
     * @param  resource  $ch
     * @return $this
     */
    public function finish($ch)
    {
        if (! $this->headerEOL) {
            $this->headerEOL = true;
            $this->headerReceived($ch);
        }

        $this->triggerEndTransmission();

        return $this;
    }

    /**
     * @param  resource  $ch
     * @return $this
     */
    protected function headerReceived($ch)
    {
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->triggerHeaderEvent($this->bufferedHeader, $httpCode);

        return $this;
    }

    /**
     * @param  \Closure  $closure
     * @return $this
     */
    public function addHeaderListener(Closure $closure)
    {
        $this->headerListeners[] = $closure;
        return $this;
    }

    /**
     * @param  \Closure  $closure
     * @return $this
     */
    public function addBufferListener(Closure $closure)
    {
        $this->contentBufferListeners[] = $closure;
        return $this;
    }

    /**
     * @param  \Closure  $closure
     * @return $this
     */
    public function addEndListener(Closure $closure)
    {
        $this->endTransmissionListeners[] = $closure;
        return $this;
    }

    /**
     * @param  string  $header
     * @param  int     $httpCode
     * @return $this
     */
    protected function triggerHeaderEvent($header, $httpCode)
    {
        foreach ($this->headerListeners as $listener) {
            $listener($httpCode, $header);
        }
        return $this;
    }

    /**
     * @param  string  $data
     * @return $this
     */
    protected function triggerBufferListener($data)
    {
        foreach ($this->contentBufferListeners as $listener) {
            $listener($data);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function triggerEndTransmission()
    {
        foreach ($this->endTransmissionListeners as $listener) {
            $listener();
        }
        return $this;
    }
}