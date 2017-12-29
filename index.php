<?php

require_once __DIR__ . '/vendor/autoload.php';

$forwarding = [
    'localhost:8080' => ['host' => 'localhost', 'ip' => '127.0.0.1'],
];

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

if (isset($forwarding[$host])) {
    $forwarding = $forwarding[$host];

    $client = \JetProxy\ClientFactory::make()->browserProxy($forwarding['host'], $forwarding['ip']);
    $client->request($method, get_path_info())->run();
}