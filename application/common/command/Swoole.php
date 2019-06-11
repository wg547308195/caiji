<?php
/**
 * Created by PhpStorm.
 * User: xuewl
 * Date: 2018/1/3
 * Time: 11:55
 */

namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class Swoole extends Command
{
    protected $server;

    protected $types = [
        'notify_message',
        'statistics',
    ];

    protected $debug = true;

    protected $table = null;

    // 命令行配置函数
    protected function configure()
    {
        // setName 设置命令行名称
        // setDescription 设置命令行描述
        $this->setName('swoole')->setDescription('Start TCP(Timer) Server!');
    }

    // 设置命令返回信息
    protected function execute(Input $input, Output $output)
    {
        $this->server = new \swoole_server(config('swoole.host'), config('swoole.port'));
        // server 运行前配置
        $this->server->set([
            'worker_num'      => config('swoole.worker_num'),
            'daemonize'       => config('swoole.daemonize'),
            'task_worker_num' => config('swoole.task_worker_num')
        ]);

        $this->table = new \swoole_table(1024);
        $this->table->column('time', \swoole_table::TYPE_INT, 11);
        $this->table->create();

        // 注册回调函数
        $this->server->on('Start', [$this, 'onStart']);
        $this->server->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->server->on('Connect', [$this, 'onConnect']);
        $this->server->on('Receive', [$this, 'onReceive']);
        $this->server->on('Task', [$this, 'onTask']);
        $this->server->on('Finish', [$this, 'onFinish']);
        $this->server->on('Close', [$this, 'onClose']);
        $this->server->start();
    }

    // 主进程启动时回调函数
    public function onStart(\swoole_server $server)
    {
        echo "[ " . date('Y-m-d H:i:s') . " ] 系统数据交互节点启动完毕" . PHP_EOL;
    }

    public function onWorkerStart(\swoole_server $server, $worker_id)
    {
        // 仅在第一个 Worker 进程启动时启动 Timer 定时器
        if (!isset($this->types[$worker_id])) {
            return false;
        }
        $worker = $this->types[$worker_id];

        switch ($worker) {
            case 'notify_message':
                swoole_timer_tick(1000, function () use ($worker) {
                    //判断锁
                    $val = $this->table->get($worker);

                    if (isset($val['time'])) {
                        //如果有值 而且 时间小于60秒 就返回 否则就删除锁
                        if (time() - $val['time'] < 1) {
                            echo "所存在 被忽略";
                            return false;
                        } else {
                            $this->table->del($worker);
                        }
                    }

                    //查找数据
                    $items = \think\Db::name('sys_notify_message')->where('status', 0)->order('id', 'asc')->limit(10)->select();

                    //加锁
                    $this->table->set($worker, array('time' => time()));

                    echo "[ " . date('Y-m-d H:i:s') . " ][ 执行任务 ]；共有 " . count($items) . " 条记录满足！类型：notify_message" . PHP_EOL;

                    $ids = \fast\ArrayHelper::get_ids_arr($items, 'id');
                    // 设置状态
                    $result = \think\Db::name("sys_notify_message")->where('id', 'in', $ids)->update(['status' => 1]);

                    //删锁
                    $this->table->del($worker);

                    if ($result != count($ids)) {
                        echo "\t[ 执行任务 ] ；执行失败（" . $result . "/" . count($ids) . "）" . PHP_EOL;
                        return false;
                    }

                    foreach ($items as $item) {
                        $this->server->task($item);
                    }

                });
                break;
            case 'statistics' :
                swoole_timer_tick(1000, function () use ($worker) {
                    //判断锁
                    $val = $this->table->get($worker);

                    if (isset($val['time'])) {
                        //如果有值 而且 时间小于60秒 就返回 否则就删除锁
                        if (time() - $val['time'] < 1) {
                            echo "所存在 被忽略";
                            return false;
                        } else {
                            $this->table->del($worker);
                        }
                    }
                    //查找数据
                    $items = \think\Db::name('trigger')->where('status', 0)->order('id', 'asc')->limit(10)->select();
                    //加锁
                    $this->table->set($worker, array('time' => time()));

                    echo "[ " . date('Y-m-d H:i:s') . " ][ 执行任务 ]；共有 " . count($items) . " 条记录满足！类型：statistics" . PHP_EOL;
                    $ids = \fast\ArrayHelper::get_ids_arr($items, 'id');
                    // 设置状态
                    $result = \think\Db::name("trigger")->where('id', 'in', $ids)->update(['status' => 1]);
                    //删锁
                    $this->table->del($worker);
                    if ($result != count($ids)) {
                        echo "\t[ 执行任务 ] ；执行失败（" . $result . "/" . count($ids) . "）" . PHP_EOL;
                        return false;
                    }
                    foreach ($items as $item) {
                        $this->server->task($item);
                    }

                });
                break;
        }

    }

    // 异步任务处理函数
    public function onTask(\swoole_server $server, int $task_id, int $worker_id, $item)
    {
        $worker = $this->types[$worker_id];
        echo "[ " . date('Y-m-d H:i:s') . " ][ 执行任务 ] 任务ID：" . $task_id . "；类型：$worker" . PHP_EOL;

        switch ($worker) {
            case 'notify_message':
                //处理数据
                $result = model('push/notify_message', 'service')->task($item);
                if ($result == true) {
                    return $item;
                }
                return false;
                break;
            case 'statistics' :
                //处理数据
                $item['table'] = explode('_', $item['table']);

                $new_string = '';
                foreach ($item['table'] as $k => $v) {
                    $new_string .= ucfirst($v);
                }
                $class_name = "\\app\\statistics\\asynchronous\\" . $new_string;
                if (!class_exists($class_name)) {
                    return false;
                }
                $class = new $class_name;

                if (empty($item['event'])) {
                    $result = $class->run($item['pk']);
                } else {
                    $item['args'] = json_decode($item['args'], true);
                    $function_name = $item['event'];
                    $result = $class->$function_name($item['pk'], $item['args']);
                }

                if ($result == true) {
                    return $item;
                } else {
                    //失败
                    \think\Db::name("trigger")->where('id', '=', $item['id'])->update(['status' => 0]);
                }
                return false;
                break;
        }

    }

    // 异步任务完成通知 Worker 进程函数
    public function onFinish(\swoole_server $server, $task_id, $data)
    {

        $worker = $this->types[$this->server->worker_id];

        switch ($worker) {
            case 'notify_message':
                //完成数据
                \think\Db::name("sys_notify_message")->where('id', '=', $data['id'])->delete();
                break;
            case 'statistics' :
                //完成数据
                \think\Db::name("trigger")->where('id', '=', $data['id'])->delete();
                break;
        }
        echo PHP_EOL . "[完成任务] 任务ID：" . $task_id . "；类型" . PHP_EOL;

    }

    // 建立连接时回调函数
    public function onConnect(\swoole_server $server, $fd, $from_id)
    {
    }

    // 收到信息时回调函数
    public function onReceive(\swoole_server $server, $fd, $from_id, $data)
    {
    }

    // 关闭连时回调函数
    public function onClose(\swoole_server $server, $fd, $from_id)
    {
    }

    /**
     * 控制台输出
     * @param      $message
     * @param bool $exit
     */
    protected function debug($message, $exit = false)
    {
        echo $message . PHP_EOL;
        $exit && exit();
    }

    /**
     * 线程加锁
     * @param string $timer_id 线程类别
     * @param int    $seconds  有效期
     * @return bool
     */
    protected function lock($timer_id = "", $seconds = 60)
    {
    }

    /**
     * 线程解锁
     * @param $timer_id
     * @return bool
     */
    protected function unlock($timer_id)
    {
    }
}
