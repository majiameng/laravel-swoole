<?php
namespace SwooleTW\Server;
use App\Services\swoole\SwooleWebSocket;

class WebSocket{
    /**
     * 配置
     * Author: JiaMeng <666@majiameng.com>
     * @var array
     */
    private $setting = [];

    public function __construct() {
        /**
         * websocket链接 wss://mywss.majiameng.com:1313
         * 当前socket链接 ws://www.majiameng.com:1233
         */
        $this->setting = config('config.swooleAsync');
        $this->check();
    }

    /**
     * [check description]
     * @return [type] [description]
     */
    private function check(){
        /**
         * 检测 PDO_MYSQL
         */
        if (!extension_loaded('pdo_mysql')) {
            exit('error:请安装PDO_MYSQL扩展' . PHP_EOL);
        }
        /**
         * 检查exec 函数是否启用
         */
        if (!function_exists('exec')) {
            exit('error:exec函数不可用' . PHP_EOL);
        }
        /**
         * 检查命令 lsof 命令是否存在
         */
        exec("whereis lsof", $out);
        if (strpos($out[0], "/usr/sbin/lsof") === false ) {
            exit('error:找不到lsof命令,请确保lsof在/usr/sbin下' . PHP_EOL);
        }
        /**
         * 检查目录是否存在并赋予权限
         */
        if(!is_dir($this->setting['log_dir'])) {
            mkdir($this->setting['log_dir'], 0777, true);
        }
    }

    /**
     * Description: 启动服务
     * Author: JiaMeng <666@majiameng.com>
     * Updater:
     */
    public function serviceStart(){
        /**
         * websocket链接 wss://mywss.majiameng.com:1313
         * 当前socket链接 ws://www.majiameng.com:1233
         */
        $pidfile = $this->setting['pidfile'];
        $host = $this->setting['host'];
        $port = $this->setting['port'];

        /** 检测上次是否异常退出 */
        if (!is_writable(dirname($pidfile))) {
            $this->error("pid文件需要写入权限".dirname($pidfile));
        }
        if (file_exists($pidfile)) {
            $pid = explode("\n", file_get_contents($pidfile));
            $cmd = "ps ax | awk '{ print $1 }' | grep -e \"^{$pid[0]}$\"";
            exec($cmd, $out);
            if (!empty($out)) {
                $this->msg("[warning]:pid文件已存在,服务已经启动,进程id为:{$pid[0]}",true);
            } else {
                $this->msg("[warning]:pid文件已存在,可能是服务上次异常退出");
                unlink($pidfile);
            }
        }
        $bind = $this->bindPort($port);
        if ($bind) {
            foreach ($bind as $k => $v) {
                if ($v['ip'] == '*' || $v['ip'] == $host) {
                    $this->error("服务启动失败,{$host}:{$port}端口已经被进程ID:{$k}占用");
                }
            }
        }

        SwooleWebSocket::getInstance($this->setting)->serviceStart();
    }


    /**
     * Description:  获取指定端口的服务占用列表
     * Author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param $port
     * @return array
     */
    private function bindPort($port) {
        $res = [];
        $cmd = "/usr/sbin/lsof -i :{$port}|awk '$1 != \"COMMAND\"  {print $1, $2, $9}'";//如果报错 请安装: yum install lsof
        exec($cmd, $out);
        if ($out) {
            foreach ($out as $v) {
                $a = explode(' ', $v);
                list($ip, $p) = explode(':', $a[2]);
                $res[$a[1]] = [
                    'cmd'  => $a[0],
                    'ip'   => $ip,
                    'port' => $p,
                ];
            }
        }
        return $res;
    }

    /**
     * Description:  暂停服务
     * Author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return null
     */
    public function serviceStop(){

        $pidfile = $this->setting['pidfile'];

        $this->msg("正在停止服务...");

        if (!file_exists($pidfile)) {
            $this->error("pid文件:". $pidfile ."不存在");
        }
        $pid = explode("\n", file_get_contents($pidfile));

        if ($pid[0]) {
            $cmd = "kill {$pid[0]}";
            exec($cmd);
            do {
                $out = [];
                $c = "ps ax | awk '{ print $1 }' | grep -e \"^{$pid[0]}$\"";
                exec($c, $out);
                if (empty($out)) {
                    break;
                }else{
                    exec("kill -9 {$pid[0]}");
                }
            } while (true);
        }

        //确保停止服务后swoole-task-pid文件被删除
        if (file_exists($pidfile)) {
            unlink($pidfile);
        }
        $this->msg("服务已停止");
        return $this;
    }


    /**
     * Description:  查看服务状态
     * Author: JiaMeng <666@majiameng.com>
     * Updater:
     */
    public function serviceStatus(){

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

    /**
     * Description:  serviceList
     * Author: JiaMeng <666@majiameng.com>
     * Updater:
     */
    public function serviceList(){

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
