<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Room drivers settings
    |--------------------------------------------------------------------------
    */
    'settings' => [
        'host' => '0.0.0.0',                        //服务启动IP
        'port' => '1233',                           //服务启动端口
        'swoole_http' => 'http://majiameng.com:1233',//推送触发连接地址
        'process_name' => 'majiamengsocket',        //服务进程名
//        'open_tcp_nodelay' => '1',                //启用open_tcp_nodelay(注:启用后会增加缓存,做即时通讯的话会有延迟,不建议开启)
        'daemonize' => false,                       //守护进程化
        'heartbeat_idle_time' => 180,               //客户端向服务端请求的间隔时间
        'heartbeat_check_interval' => 120,          //服务端向客户端发送心跳包的间隔时间
        'dispatch_mode' => 2,                       //数据包分发策略
//        'reactor_num' => 8,                       //线程数
//        'worker_num' => 8,                        //work进程数目
//        'task_worker_num' => 8,                   //task进程的数量
        'task_max_request' => '10000',              //work进程最大处理的请求数
        'max_connection' => '10000',                //服务器程序，最大允许的连接数
        'buffer_output_size' => 32 * 1024 *1024,    //配置发送输出缓存区内存尺寸
        'socket_buffer_size' => 4 * 1024 * 1024,    //配置客户端连接的缓存区长度
        'pidfile' => '/data/frame/laravel5.6/swoole/swoole_socket.pid',//(注:如果守护进程化,日志文件需要全路径)
        'log_dir' => './swoole',
        'task_tmpdir' => './swoole',
        'log_file' => './swoole/swoole_socket.log',
        'log_size' => 204800000,                    //运行时日志 单个文件大小
    ]
];
