<?php

$host = getenv('JET_PROXY_HOST');
$ip   = getenv('JET_PROXY_IP') ?: gethostbyname($host);

require_once __DIR__ . '/vendor/autoload.php';

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

$client = \JetProxy\ClientFactory::make()->browserProxy($host, $ip);
try {
    $client->request($method, get_path_info())->run();
} catch (\JetProxy\ClientRequestErrorException $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo $e->getMessage();
}
