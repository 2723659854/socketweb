<?php
require_once __DIR__ . '/Timer.php';
use Root\Timer;
Timer::add(1, function () {
    echo date('Y-m-d H:i:s');
    echo "\r\n";
}, [], true);

Timer::run();
//保持常驻进程
while (1) {
    //捕捉信号
    pcntl_signal_dispatch();
    sleep(3);
}
