<?php
namespace App\Services\swoole;
class SwooleWebSocket{
    /**
     * SwooleWebSocket class 对象
     * Author: JiaMeng <666@majiameng.com>
     * @var null|SwooleWebSocket
     */
    private static $inst = null;// WebSocket对象

    /**
     * swoole_websocket_server
     * Author: JiaMeng <666@majiameng.com>
     * @var null|\swoole_websocket_server
     */
    public $server = null;

    /**
     * 配置
     * Author: JiaMeng <666@majiameng.com>
     * @var array
     */
    private $setting = [];

    /**
     * SwooleWebSocket constructor.
     * @param $config
     */
    public function __construct($config) {
        /** 加载配置文件 */
        $this->setting = $config;
        $this->server = new \swoole_websocket_server($this->setting['host'], $this->setting['port']);
    }

    /**
     * Description:  空的克隆方法，防止被克隆
     * Author: JiaMeng <666@majiameng.com>
     * Updater:
     */
    private function __clone() {}

    /**
     * Description:  getInstance
     * Author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return SwooleWebSocket|null
     */
    static public function getInstance($config=[]){
        if((self::$inst instanceof self) ==  FALSE){
            self::$inst = new self($config);
        }
        return self::$inst;
    }

    /**
     * Description: 启动服务
     * Author: JiaMeng <666@majiameng.com>
     * Updater:
     */
    public function serviceStart(){
        $this->msg('服务正在启动...');
        /** 载入配置文件 */
        $this->server->set($this->setting);

        /** 回调函数 */
        $call = [
            'open',/** 用户连接 */
            'close',/** 用户断开连接 */
            'message',/** 用户接收消息 */
            'request',/** 主动推送 */
            'start',/** 服务启动时触发 */
//            'workerStart',
//            'managerStart',
//            'task',
//            'finish',
//            'receive',
//            'workerStop',
//            'shutdown',
        ];
        //事件回调函数绑定
        foreach ($call as $v) {
            $m = 'on' . ucfirst($v);
            if (method_exists($this, $m)) {
                $this->server->on($v, [$this, $m]);
            }
        }

        $this->msg("服务启动成功");
        $this->msg("服务运行名称:{$this->setting['process_name']}");
        $this->msg("服务运行端口:{$this->setting['host']}:{$this->setting['port']}");
        $this->server->start();
    }

    /**
     * Description:  查看服务状态
     * Author: JiaMeng <666@majiameng.com>
     * Updater:
     */
    public function serviceStats(){

        $cmd = "ps aux|grep " . $this->setting['process_name'] . "|grep -v grep|awk '{print $1, $2, $6, $8, $9, $11}'";
        exec($cmd, $out);

        if (empty($out)) {
            $this->msg("没有发现正在运行服务",true);
        }

        $this->msg("本机运行的服务进程列表:");
        $this->msg("USER PID RSS(kb) STAT START COMMAND");

        foreach ($out as $v) {
            $this->msg($v);
        }

    }

/** ***************************↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓ 以下是触发 ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓************************************************** */

    /**
     * Description:  启动时触发
     * Author: JiaMeng <666@majiameng.com>
     * Updater:
     */
    public function onStart($server){
        echo '[' . date('Y-m-d H:i:s') . "]\t swoole_websocket_server master worker start\n";
        $this->setProcessName($this->setting['process_name'] . '-master');
        //记录进程id,脚本实现自动重启
        $pid = "{$this->server->master_pid}\n{$this->server->manager_pid}";
        file_put_contents($this->setting['pidfile'], $pid);
    }

    /**
     * 设置swoole进程名称
     * @param string $name swoole进程名称
     */
    private function setProcessName($name){
        if (function_exists('cli_set_process_title')) {
            cli_set_process_title($name);
        } else {
            if (function_exists('swoole_set_process_name')) {
                swoole_set_process_name($name);
            } else {
                trigger_error(__METHOD__. " failed.require cli_set_process_title or swoole_set_process_name.");
            }
        }
    }

    /**
     * Description:  连接时触发
     * Author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param $server
     * @param $request
     */
    public function onOpen($server, $request){
        echo "server: websocketclient success with fd{$request->fd}\n";
        $infos = ['code' => 200, 'status' => 1, 'command' => 'whoAreU'];
    }

    /**
     * Description:  接收消息时触发
     * Author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param $server
     * @param $frame
     */
    public function onMessage($server, $frame){
        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";

    }

    /**
     * Description:  连接断开时触发
     * Author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param $server
     * @param $frame
     */
    public function onClose($server, $frame){
        echo "client {$frame} closed\n";
    }

    /**
     * Description:  主动推送
     * Author: JiaMeng <666@majiameng.com>
     * Updater:
     */
    public function onRequest($request, $response){
        /**
         * 主动推送  http://www.majiameng.com:1233/?message=456789
         * 接收http请求从get获取message参数的值，给用户推送\
         * $this->server->connections 遍历所有websocket连接用户的fd，给所有用户推送
         */
        foreach ($this->server->connections as $fd) {
            $this->server->push($fd, $request->get['message']);
        }
    }

    /**
     * Description:  输出消息
     * Author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param $msg
     * @param bool $exit
     */
    private function msg($msg,$exit=false){
        if($exit){
            exit($msg . PHP_EOL);
        }else{
            echo $msg . PHP_EOL;
        }
    }

    /**
     * Description:  输出错误消息
     * Author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param $msg
     */
    private function error($msg){
        exit("[error]:".$msg . PHP_EOL);
    }
}
