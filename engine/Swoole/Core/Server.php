<?php

namespace Swoole\Core;

class Server
{
    /**
     * The Swoole Server Object.
     *
     * @var \swoole_server
     */
    protected $serv = null;

    /**
     * The Swoole Server Config.
     *
     * @var array
     */
    protected $swoole_config = null;

    /**
     * The Process Name.
     *
     * @var string
     */
    protected $name = null;

    public function __construct()
    {
        $config = Di::get('config');
        if (isset($config['name'])) {
            $this->name = $config['name'];
        }
        $this->swoole_config = Di::get('swoole_config');
        $this->serv = new \swoole_server($config['host'], $config['port']);
    }

    public function configure($key, $value)
    {
        $this->swoole_config[$key] = $value;
        return $this;
    }

    public function getServ()
    {
        return $this->serv;
    }

    public function onStart()
    {
        $prefix = $this->name ? $this->name.': ' : '';
        \swoole_set_process_name($prefix.'rpc master');
    }

    public function onManagerStart()
    {
        $prefix = $this->name ? $this->name.': ' : '';
        \swoole_set_process_name($prefix.'rpc manager');
    }

    public function onWorkerStart($serv, $worker_id)
    {
        $prefix = $this->name ? $this->name.': ' : '';
        \swoole_set_process_name($prefix.'rpc worker');
        Logger::init("/tmp/{$this->name}-{$worker_id}.log");

        \ORM::set_db(null, 'default');
        \ORM::get_db('default');
    }

    public function onReceive($serv, $fd, $from_id, $data)
    {

    }

    public function onWorkerStop($serv, $worker_id)
    {
        $code = $serv->getLastError();
        Logger::write("onWorkerStop-{$worker_id}-{$code}");
        \ORM::set_db(null, 'default');
    }

    public function onWorkerError($serv, $worker_id, $worker_pid, $exit_code, $signal)
    {
        Logger::write("onWorkerError-{$worker_id}-{$worker_pid}-{$exit_code}-{$signal}");
        \swoole_process::kill($worker_id);
        \ORM::set_db(null, 'default');
        Logger::reset();
    }

    public function onWorkerExit($serv, $worker_id)
    {
        Logger::reset();
    }

    public function serve()
    {
        $db = include ROOT.'/config/database.php';
        foreach ($db as $name => $option) {
            \ORM::configure($option, null, $name);
        }

        $support_callback = [
            'start' => [$this, 'onStart'],
            'managerStart' => [$this, 'onManagerStart'],
            'workerStart' => [$this, 'onWorkerStart'],
            'receive' => [$this, 'onReceive'],
            'task' => null,
            'finish' => null,
            'workerStop' => [$this, 'onWorkerStop'],
            'workerError' => [$this, 'onWorkerError'],
            'workerExit' => [$this, 'onWorkerExit'],
        ];
        foreach ($support_callback as $name => $callback) {
            
            // If has the dependency injection
            
            if (is_callable(Di::get($name))) {
                $callback = Di::get($name);
            }

            if ($callback !== null) {
                $this->serv->on($name, $callback);
            }
        }
        $this->serv->set($this->swoole_config);
        $this->serv->start();
    }
}
