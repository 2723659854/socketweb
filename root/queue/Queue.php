<?php

namespace Root\Queue;

use Redis;

class Queue
{
    /**
     * 返回任务类 类名
     * @return false|string
     */
    public static function name()
    {
        return get_called_class();
        //get_class
    }

    /**
     * 队列消费者，具体业务逻辑在这里面实现
     */
    public function handle()
    {
    }

    /**
     * 队列生产者
     * @param array $param 业务所需参数，使用一个数组传递
     * @param int $delay 延迟时间，不填或者0表示普通队列，大于0表示延迟秒数
     */
    public static function dispatch($param = [], $delay = 0)
    {
        $config = config('redis');
        $host   = isset($config['host']) ? $config['host'] : '127.0.0.1';
        $port   = isset($config['port']) ? $config['port'] : '6379';
        $client = new Redis();
        $client->connect($host, $port);
        //$class=__CLASS__;
        $class = self::name();
        if ($delay > 0) {
            //防止同一时刻多个用户发送相同的任务
            $param['rand'] = uniqid();
            $client->zAdd('xiaosongshu_delay_queue', ['NX'], time() + $delay, json_encode(['class' => $class, 'param' => $param]));
        } else {
            $client->LPUSH('xiaosongshu_queue', json_encode(['class' => $class, 'param' => $param]));
        }
        $client->close();
    }


}