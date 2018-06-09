<?php

namespace SwooleTW\Http\Commands;

use Illuminate\Console\Command;
//use Swoole\Process;

class HttpServerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
//    protected $signature = 'swoole:http {action : start|stop|restart|reload|infos}';
    protected $signature = 'swoole:socket {operation=start}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Swoole Socket Server controller.';

    /**
     * The console command action. start|stop|restart|reload
     *
     * @var string
     */
    protected $action;

    /**
     *
     * The pid.
     *
     * @var int
     */
    protected $pid;

    /**
     * The configs for this package.
     *
     * @var array
     */
    protected $configs;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {

        /** 接收操作命令 */
        $operation = $this->argument('operation');
        echo $operation;
//        $webSocket = new WebSocket();
//        switch ($operation) {
//            case 'start':
//                $webSocket->serviceStart();
//                break;
//            case 'stop':
//                $webSocket->serviceStop();
//                break;
//            case 'restart':
//                $webSocket->serviceStop()->serviceStart();
//                break;
//            case 'status':
//                $webSocket->serviceStatus();
//                break;
//            case 'list':
//                $webSocket->serviceList();
//                break;
//            default:
//                exit('error:参数错误');
//                break;
//        }
    }

    /**
     * Load configs.
     */
    protected function loadConfigs()
    {
        $this->configs = $this->laravel['config']->get('swoole_http');
    }



}
