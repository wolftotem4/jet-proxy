<?php

require_once __DIR__ . '/vendor/autoload.php';

$client = new JetProxy\Client('r88.test', '127.0.0.1');

$client->request('/sy2/images/royal88/logo-small.png');