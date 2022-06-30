<?php
$param     = $argv;
$daemonize = false;//是否已守护进程模式运行
$flag      = true;//是否结束脚本运行
global $pid_file, $log_file;
$pid_file = './my_pid.txt';//pid存放文件
$log_file = './log.txt';//业务逻辑存放文件
//检测是否是windows运行环境
$system = true;//Linux系统
if (\DIRECTORY_SEPARATOR === '\\') {
    $system = false;//windows系统
}
if (count($param) > 1) {
    switch ($param[1]) {
        case "start":
            //守护进程模式运行
            if (isset($param[2]) && ($param[2] == '-d')) {
                if ($system) {
                    $daemonize = true;
                } else {
                    echo "当前环境是windows,只能在控制台运行\r\n";
                }
            }
            echo "进程启动...\r\n";
            break;
        case "stop":
            if ($system) {
                //关闭进程
                if (file_exists($pid_file)) {
                    $master_id = file_get_contents($pid_file);
                    if ($master_id > 0) {
                        \posix_kill($master_id, SIGKILL);
                    }
                }
                echo "关闭进程中...\r\n";
            } else {
                echo "当前环境是windows,只能在控制台运行\r\n";
            }
            $flag = false;
            break;
        case "restart":
            //重启进程
            if ($system) {
                if (file_exists($pid_file)) {
                    $master_id = file_get_contents($pid_file);
                    if ($master_id > 0) {
                        \posix_kill($master_id, SIGKILL);
                    }
                }
                $daemonize = true;
                echo "进程已重启\r\n";
            } else {
                echo "当前环境是windows,只能在控制台运行\r\n";
            }
            break;
        default:
            echo "未识别的命令\r\n";
            $flag = false;
    }
} else {
    echo "缺少必要参数，你可以输入start,start -d,stop,restart\r\n";
    $flag = false;
}

//中间件，控制程序是否继续运行
if ($flag == false) {
    exit("进程已关闭，脚本退出运行\r\n");
}

//这里采用文件添加独占锁的形式，如果一个进程在后台运行，这个文件被占用了，这个脚本就不能往下执行了，只有这个进程被关闭后才会被释放
if ($daemonize) {
    $fd = fopen('./lock.txt', 'w');
    //这里必须是非阻塞写入，否则进程一直挂在这里不动了
    $res = flock($fd, LOCK_EX | LOCK_NB);
    if (!$res) {
        echo "进程正在运行，请勿重复启动\r\n";
        exit(0);
    }
}
//业务逻辑代码示例，用于观测脚本是否正在运行，具体业务逻辑自己实现
function say()
{
    global $log_file;
    while (true) {
        $fp = fopen($log_file, 'a+');
        //记录当前进程id和时间，判断是否是单进程运行
        fwrite($fp, time() . "----" . getmypid() . "\r\n");
        fclose($fp);
        sleep(2);
    }
}

//以守护进程模式运行
function daemon()
{
    //重设文件掩码为0，就是文件权限为0
    \umask(0);
    //创建子进程
    $pid = \pcntl_fork();
    if (-1 === $pid) {
        //创建子进程失败
        throw new Exception('Fork fail');
    } elseif ($pid > 0) {//
        //关闭主进程
        exit(0);
    }
    global $pid_file;
    //将进程pid写入到文件当中，方便关闭进程，重启进程
    file_put_contents($pid_file, getmypid());

    //setsid();   //使子进程独立1.摆脱原会话控制 2.摆脱原进程组的控制 3.摆脱控制终端的控制，4，升级子进程为主进程
    if (-1 === \posix_setsid()) {
        throw new Exception("Setsid fail");
    }
    say();
    //再次创建一个子进程，Fork再次避免系统重新控制终端
    $pid = \pcntl_fork();
    if (-1 === $pid) {
        throw new Exception("Fork fail");
    } elseif (0 !== $pid) {
        //不管是子进程还是主进程都退出
        exit(0);
    }
}

//运行程序
if ($daemonize) {
    daemon();
} else {
    say();
}