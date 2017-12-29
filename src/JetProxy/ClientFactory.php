<?php

namespace JetProxy;

class ClientFactory
{
    /**
     * @return static
     */
    public static function make()
    {
        return new static();
    }

    /**
     * @param  $host
     * @param  string|null  $ipAddress
     * @param  string       $protocol
     * @param  int|null     $port
     * @return \JetProxy\ClientInterface
     */
    public function browserProxy($host, $ipAddress = null, $protocol = 'http', $port = null)
    {
        $client = new Client($host, $ipAddress, $protocol, $port);
        return new BrowserResponseProxy(new BrowserRequestProxy($client));
    }
}