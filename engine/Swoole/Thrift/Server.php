<?php

namespace Swoole\Thrift;

use Thrift;
use Thrift\Server\TSimpleServer;
use Swoole\Core\Di;
use Swoole\Core\Logger;

class Server extends TSimpleServer
{

    public function onReceive($serv, $fd, $from_id, $data)
    {
        $config = Di::get('config');
        $processor_class = $config['processor'];
        $handler_class = $config['handler'];

        $handler = new $handler_class();
        $processor = new $processor_class($handler);

        $socket = new Socket();
        $socket->setHandle($fd);
        $socket->buffer = $data;
        $socket->server = $serv;
        $protocol = new Thrift\Protocol\TBinaryProtocol($socket, false, false);

        try {
            $protocol->fname = $config['name'];
            $processor->process($protocol, $protocol);
        } catch (\Exception $e) {
            Logger::write(date('Y-m-d H:i:s').' ['.$e->getCode().'] '. $e->getMessage().PHP_EOL);
        }
    }
}
