<?php
// 引入客户端文件
require_once __DIR__ . '/../Thrift/ClassLoader/ThriftClassLoader.php';
use Thrift\ClassLoader\ThriftClassLoader;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TSocket;
use Thrift\Transport\TFramedTransport;

$thrift_dirname = dirname(__DIR__);

$loader = new ThriftClassLoader();
$loader->registerNamespace('Thrift', $thrift_dirname);
$loader->registerNamespace('Swoole', $thrift_dirname);
$loader->registerNamespace('Services', __DIR__);
$loader->registerDefinition('Services',  __DIR__);
$loader->register();

$socket = new TSocket('127.0.0.1', 8091);
$transport = new TFramedTransport($socket);
$protocol = new TBinaryProtocol($transport);
$transport->open();

$client = new Services\HelloSwoole\HelloSwooleClient($protocol);
$message = new Services\HelloSwoole\Message(array('send_uid' => 101, 'name' => 'rango'));
$ret = $client->sendMessage($message);
var_dump($ret);

$transport->close();
