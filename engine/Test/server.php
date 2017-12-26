<?php

require_once __DIR__ . '/../Thrift/ClassLoader/ThriftClassLoader.php';
use Thrift\ClassLoader\ThriftClassLoader;
use Thrift\Server\TServerSocket;
use Swoole\Core\App;

$thrift_dirname = dirname(__DIR__);

/* no composer version */
$loader = new ThriftClassLoader();
$loader->registerNamespace('Thrift', $thrift_dirname);
$loader->registerNamespace('Swoole', $thrift_dirname);
$loader->registerNamespace('Services', __DIR__);
$loader->registerDefinition('Services',  __DIR__);
$loader->register();

$service = new Services\HelloSwoole\Handler();
$processor = new Services\HelloSwoole\HelloSwooleProcessor($service);

$config = include __DIR__ . '/Conf/config.php';
$swoole_config = include __DIR__ . '/Conf/swoole.php';

$app = new App($config, $swoole_config);
$app->initRPC($processor);
$app->run();
