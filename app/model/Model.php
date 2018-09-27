<?php

class Model
{
    protected $_database = 'default';
    protected $_table = '';

    // orm instance for model
    protected $_instance;

    public function __construct($table = '', $database = '')
    {
        if ($table) {
            $this->_table = $table;
        }
        if ($database) {
            $this->_database = $database;
        }
        if (!$this->_table) {
            $this->_table = strtolower(get_called_class());
        }

        $this->PDOPing(\ORM::get_db($this->_database));
        $this->_instance = \ORM::for_table($this->_table, $this->_database);
    }

    private function PDOPing(\PDO $conn)
    {
        try {
            $conn->getAttribute(\PDO::ATTR_SERVER_INFO);
        } catch (\PDOException $e) {
            if ($e->getCode() == 'HY000') {
                \ORM::set_db(null, $this->_database);
                file_put_contents("/tmp/ping.log", $e->getCode().'---'.$e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    public function __get($key)
    {
        return isset($this->_instance->$key) ? $this->_instance->$key : null;
    }

    public function __set($key, $value)
    {
        $this->_instance->$key = $value;
    }

    public function __call($method, $args)
    {
        if ($this->_instance && method_exists($this->_instance, $method)) {
            return call_user_func_array(array($this->_instance, $method), $args);
        }

        return false;
    }

    public function clean()
    {
        $this->_instance = \ORM::for_table($this->_table, $this->_database);
        return $this;
    }
}
