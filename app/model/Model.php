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
        try {
            $this->_instance = \ORM::for_table($this->_table, $this->_database);
        } catch (Exception $e){
            \ORM::set_db(null, $this->_database);
            \ORM::get_db($this->_database);
            file_put_contents("/tmp/aaa-exp", var_export($e,true));
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
            try {
                return call_user_func_array(array($this->_instance, $method), $args);
            } catch (Exception $e) {
                if (1) {
                    // 如果mysql gone away，自动重连
                    file_put_contents("/tmp/aaa-exp", var_export($e,true));
                    \ORM::set_db(null, $this->_database);
                    \ORM::get_db($this->_database);
                    return call_user_func_array(array($this->_instance, $method), $args);
                }
                throw new Exception($e->getMessage(), $e->getCode());
            }
        } else {
            return false;
        }
    }

    public function clean()
    {
        try {
            $this->_instance = \ORM::for_table($this->_table, $this->_database);
        } catch (Exception $e){
            \ORM::set_db(null, $this->_database);
            \ORM::get_db($this->_database);
            file_put_contents("/tmp/aaa-exp", var_export($e,true));
        }
        return $this;
    }
}
