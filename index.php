<?php

require_once __DIR__ . '/vendor/autoload.php';

$forwarding = [
    'jet-proxy.test:8080' => ['host' => 'localhost', 'ip' => '127.0.0.1'],
];

$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

if (isset($forwarding[$host])) {
    $forwarding = $forwarding[$host];

    $client = new JetProxy\Client($forwarding['host'], $forwarding['ip']);
    $client->request(get_path_info());
}