<?php

require_once __DIR__ . '/vendor/autoload.php';

$forwarding = [
    // Edit the forwarding list below.
    'localhost:8080' => ['host' => 'localhost', 'ip' => '127.0.0.1'],
  
    // You can disguise as an actual website.  (Please don't do anything evil.)
    // Don't forget you need to set up `/etc/hosts` (Linux)
  	// or '%systemroot%\System32\drivers\etc\hosts' (Windows).
    // 'example.com' => ['host' => 'example.com', 'ip' => '123.123.123.123'],
];

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

if (isset($forwarding[$host])) {
    $forwarding = $forwarding[$host];

    $client = \JetProxy\ClientFactory::make()->browserProxy($forwarding['host'], $forwarding['ip']);
    $client->request($method, get_path_info())->run();
}