<?php

namespace JetProxy;

interface ClientInterface
{
    /**
     * @param  string  $method
     * @param  string  $uri
     * @return \JetProxy\Request
     */
    public function request($method, $uri);
}