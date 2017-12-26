<?php

use Swoole\Core\App;

define('ROOT', __DIR__.'/..');

require ROOT.'/vendor/autoload.php';

$service = new Authority\Handler();
$processor = new Authority\AuthorityServiceProcessor($service);

$config = include ROOT.'/config/config.php';
$swoole_config = include ROOT.'/config/swoole.php';

$app = new App($config, $swoole_config);
$app->initRPC($processor);
$app->run();