<?php

namespace Services\HelloSwoole;

use Swoole\MySQL\Client;
use Swoole\Core\Logger;

class Handler implements HelloSwooleIf
{
    public function sendMessage(\Services\HelloSwoole\Message $msg)
    {
        Logger::write('hello world'.PHP_EOL);
        return ($msg->send_uid > 100) ? RetCode::SUCCESS : RetCode::ACCESS_DENY;
    }
}
