<?php

return [
    'worker_num' => 8,
    'dispatch_mode' => 1,               // 1-轮询 3-抢占
    'open_length_check' => true,        // 打开包长检测
    'package_max_length' => 8192000,    // 最大的请求包长度,8M
    'package_length_type' => 'N',       // 长度的类型，参见PHP的pack函数
    'package_length_offset' => 0,       // 第N个字节是包长度的值
    'package_body_offset' => 4,         // 从第几个字节计算长度
    // 'daemonize' => 1,
    'heartbeat_idle_time' => 600,       // 一个连接如果600秒内未向服务器发送任何数据，此连接将被强制关闭
    'heartbeat_check_interval' => 60,   // 表示每60秒遍历一次
    'reload_async' => true,             // 将启用异步安全重启特性，Worker进程会等待异步事件完成后再退出
];