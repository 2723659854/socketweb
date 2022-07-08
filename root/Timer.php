<?php

namespace Root;

/**
 *定时器
 */
class Timer
{
    /**
     * 运行原理：
     * 首先是注册一个闹钟信号，绑定一个业务
     * 然后发送一个闹钟信号，进程在捕捉到信号后，执行业务，执行完成后，再发送一个信号，后面捕捉到信号后，再次执行业务，依次循环
     *
     */
    //保存所有定时任务
    public static $task = array();
    //定时间隔
    public static $time = 1;

    /**
     *开启服务
     * @param $time int
     */
    public static function run()
    {
        //给当前进程注册一个闹钟信号
        self::installHandler();
        //每隔n秒给当前进程发送一个SIGALRM信号，
        //todo 启动定时器 这里必须和后面的循环的时间间隔保持一致，否则出现任务被反复重复多次添加，导致反复执行，或者完全不执行
        //pcntl_alarm(self::$time);
        pcntl_alarm(1);
    }

    /**
     *注册信号处理函数
     */
    public static function installHandler()
    {
        //todo 注册SIGALARM信号，意思就是说如果当收到这个SIGALRM信号后，执行那个闭包函数
        pcntl_signal(SIGALRM, array('Root\Timer', 'signalHandler'));
    }

    /**
     *信号处理函数
     */
    public static function signalHandler()
    {
        //执行具体的业务逻辑
        self::task();
        //一次信号事件执行完成后,再触发下一次,就是n秒后发送信号
        //todo  这里的时间间隔必须和启动的时间间隔一致，否则会反复重复多次添加任务，导致反复执行，或者不执行
        //pcntl_alarm(self::$time);
        pcntl_alarm(1);
    }

    /**
     *执行回调
     */
    public static function task()
    {
        if (empty(self::$task)) {//没有任务,返回
            return;
        }
        foreach (self::$task as $time => $arr) {
            $current = time();
            foreach ($arr as $k => $job) {//遍历每一个任务
                $func     = $job['func']; /*回调函数*/
                $argv     = $job['argv']; /*回调函数参数*/
                $interval = $job['interval']; /*时间间隔*/
                $persist  = $job['persist']; /*持久化*/
                if ($current == $time) {
                    //todo 如果这个任务的time等于当前时间，则说明马上执行，执行完成后删除任务
                    //当前时间有执行任务
                    //调用回调函数,并传递参数
                    call_user_func_array($func, $argv);
                    //删除该任务
                    unset(self::$task[$time][$k]);
                    if ($persist) {//如果做持久化,则写入数组,等待下次唤醒
                        self::$task[$current + $interval][] = $job;
                    }
                }

                //todo 这里有一个bug
                //如果任务间隔大于闹钟间隔，则会出现任务累加，越来越多，而且都被执行
                //如果任务间隔小于闹钟间隔，则会出现存入很多个任务，但是都没有被执行
                //所以必须是任务间隔等于闹钟间隔，所有任务才能够被执行

            }
            if (empty(self::$task[$time])) {
                unset(self::$task[$time]);
            }
        }
    }

    /**
     *添加任务
     */
    public static function add($interval, $func, $argv = array(), $persist = false)
    {
        if (is_null($interval)) {
            return;
        }
        //todo 为了解决上面的bug，设置为添加任务的时候，就同步修改启动时间间隔，循环时间间隔
        self::$time = $interval;
        $time       = time() + $interval;
        //写入定时任务
        self::$task[$time][] = array('func' => $func, 'argv' => $argv, 'interval' => $interval, 'persist' => $persist);
    }

    /**
     *删除所有定时器任务
     */
    public function dellAll()
    {
        self::$task = array();
    }
}

