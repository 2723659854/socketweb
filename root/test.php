<?php
//https://blog.51cto.com/u_11658127/3396645
set_time_limit(0);
ignore_user_abort();

define('TICK_INTERVAL', 1);

// 注册信号处理函数
$tickInt = TICK_INTERVAL;
//注册定时器
pcntl_signal(SIGALRM, function () use ($tickInt) {
    printf("Time: %s, Func: %s\n",
        date("Y-m-d H:i:s", time()), __FUNCTION__);
    fflush(STDOUT);

    // 再次发送ALARM信号触发执行下次信号处理函数
    //pcntl_alarm($tickInt);
    //3秒后再次触发定时器
    pcntl_alarm(3);
});
pcntl_signal(SIGINT, function () {
    printf("SIGINT caught\n");
    exit(0);
});

// 发送ALARM信号启动定时器,5秒后启动定时器
pcntl_alarm(5);

printf("Ticker is starting at: %s\n", date("Y-m-d H:i:s"));

// 分发信号
while (1) {
    //捕捉信号
    pcntl_signal_dispatch();
    sleep(2);
}


