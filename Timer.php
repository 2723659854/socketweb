<?php
set_time_limit(0);
/**
 *定时器
 */

class Timer

{
    //保存所有定时任务
    public static $task = array();
    //定时间隔
    public static $time = 1;
    /**
     *开启服务
     * @param $time int
     */
    public static function run($time = null)
    {
        if ($time) {
            self::$time = $time;
        }
        self::installHandler();
//        if (\function_exists('pcntl_alarm')){
//            \pcntl_alarm(1);
//        }else{
//            echo "没有 pcntl_alarm()这个方法\r\n";
//        }

    }
    /**
     *注册信号处理函数
     */
    public static function installHandler()
    {
        \pcntl_alarm(1);
        file_put_contents(__DIR__.'/a.txt','2222222');
        echo 'installHandler'."\r\n";
        if (\function_exists('pcntl_signal')) {
            echo "注入定时器\r\n";
            \pcntl_signal(\SIGALRM, array('Timer', 'signalHandler'), false);
        }else{
            echo "pcntl_signal()方法不存在";
        }
        //pcntl_signal(SIGALRM, array('Timer', 'signalHandler'));
    }
    /**
     *信号处理函数
     */
    public static function signalHandler()
    {
        file_put_contents(__DIR__.'/a.txt','定时器被执行');
        self::task();
        //一次信号事件执行完成后,再触发下一次
        pcntl_alarm(self::$time);
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
                $func = $job['func']; /*回调函数*/
                $argv = $job['argv']; /*回调函数参数*/
                $interval = $job['interval']; /*时间间隔*/
                $persist = $job['persist']; /*持久化*/
                if ($current == $time) {//当前时间有执行任务
                    //调用回调函数,并传递参数
                    call_user_func_array($func, $argv);
                    //删除该任务
                    unset(self::$task[$time][$k]);
                }
                if ($persist) {//如果做持久化,则写入数组,等待下次唤醒
                    self::$task[$current + $interval][] = $job;
                }
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
        $time = time() + $interval;
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
function say($name=''){

    file_put_contents(__DIR__.'/time.txt','BB'.time()."\r\n");
}

//$time=new Timer();
//$time::add(2,function (){
//    file_put_contents(__DIR__.'/time.txt','AA'.time()."\r\n");
//},['name'=>'tome'],false);
//$time::run();

//say();
Timer::add(1,function (){
    file_put_contents(__DIR__.'/time.txt','AA'.time()."\r\n");
},[],true);
Timer::run(1);