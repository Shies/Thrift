<?php

namespace Swoole\Core;

class Container
{
    protected $services = [];
    protected $instances = [];

    public function get($alias)
    {
        return isset($this->services[$alias]) ? $this->services[$alias] : null;
    }

    public function set($alias, $instance, $rewrite = false)
    {
        if (!isset($this->services[$alias]) || !$rewrite) {
            $this->services[$alias] = $instance;
            return true;
        }
        return false;
    }

    public function singleton($alias, $parameters = [])
    {
        if (isset($this->instances[$alias])) {
            return $this->instances[$alias];
        }

        if (isset($this->services[$alias]) && is_callable($this->services[$alias])) {
            $object = call_user_func_array($this->services[$alias], $parameters);
        } else {
            $class = new \ReflectionClass($alias);
            $object = $class->newInstanceArgs($parameters);
        }

        if ($object !== null) {
            $this->instances[$alias] = $object;
        }

        return $object;
    }

    public function make($alias, $parameters = [], $shared = false)
    {
        if ($shared && isset($this->instances[$alias])) {
            return $this->instances[$alias];
        }

        if (isset($this->services[$alias]) && is_callable($this->services[$alias])) {
            $object = call_user_func_array($this->services[$alias], $parameters);
        } else {
            $class = new \ReflectionClass($alias);
            $object = $class->newInstanceArgs($parameters);
        }

        if ($shared && $object !== null) {
            $this->instances[$alias] = $object;
        }

        return $object;
    }
}
