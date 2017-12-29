#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Process\ProcessUtils;
use Symfony\Component\Process\PhpExecutableFinder;

putenv('JET_PROXY_HOST=' . $argv[2]);
putenv('JET_PROXY_IP=' . (isset($argv[3]) ? $argv[3] : gethostbyname($argv[2])));

$phpExecutable = (new PhpExecutableFinder)->find(false);

echo 'Listening on ', $argv[1], PHP_EOL;
passthru(sprintf('%s -S %s server.php', $phpExecutable, escapeshellarg($argv[1])));
