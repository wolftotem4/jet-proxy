<?php

namespace JetProxy;

use Closure;

class CurlHttpReceiver
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
            $this->triggerHeaderEvent($this->bufferedHeader);
        }
        $this->triggerBufferListener($data);
        return strlen($data);
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
     * @param  string  $header
     * @return $this
     */
    protected function triggerHeaderEvent($header)
    {
        foreach ($this->headerListeners as $listener) {
            $listener($header);
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
}