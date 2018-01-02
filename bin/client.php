<?php

define('ROOT', __DIR__.'/..');

require ROOT.'/vendor/autoload.php';

$socket = new Thrift\Transport\TSocket('127.0.0.1', 8091);
$transport = new Thrift\Transport\TFramedTransport($socket);
$protocol = new Thrift\Protocol\TBinaryProtocol($transport);
$transport->open();
$socket->setRecvTimeout(10000);
$socket->setSendTimeout(10000);

$client = new Authority\AuthorityServiceClient($protocol);
$ret = $client->getAssignableGroup(1);
var_dump($ret);

$transport->close();
