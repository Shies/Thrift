<?php

namespace Swoole\Core;

class Logger
{
    protected static $fp;

    public static function init($filename)
    {
        if (static::$fp == null) {
            static::$fp = fopen($filename, 'a') ? : null;
        }
    }

    public static function write($message)
    {
        if (static::$fp !== null) {
            if (fwrite(static::$fp, $message) === false) {
                echo "Logger::write() return false!".PHP_EOL;
                static::reset();
            }
        }
    }

    public static function reset()
    {
        fclose(static::$fp);
        static::$fp = null;
    }
}