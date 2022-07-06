<?php
/*
 * @author yanglong
 * @date 2022年6月30日18:10:56
 * @description php守护进程模式运行原理
 * */
$param = $argv;
ini_set('memory_limit',-1);
$daemonize = false;//是否已守护进程模式运行
$flag = true;//是否结束脚本运行
global $pid_file, $log_file, $_port,$_listen,$_server_num;
require_once __DIR__.'/root/Timer.php';
$server = include __DIR__ . '/config/server.php';
if (isset($server['port']) && $server['port']) {
    $_port = intval($server['port']);
} else {
    $_port = 8020;
}
if (isset($server['num']) && $server['num']) {
    $_server_num = intval($server['num']);
} else {
    $_server_num = 2;
}
$_listen="http://127.0.0.1:".$_port;
$httpServer = null;
$need_close = false;//是否需要关闭进程
$pid_file = __DIR__.'/my_pid.txt';//pid存放文件
$log_file = __DIR__.'/log.txt';//业务逻辑存放文件
if (file_exists($log_file)){
    $log=file_get_contents($log_file)."\r\n===================start==============\r\n";
}else{
    $log=''."\r\n===================start==============\r\n";
}
file_put_contents($log_file,$log);
//检测是否是windows运行环境
$system = true;//Linux系统
$httpServer = null;
if (\DIRECTORY_SEPARATOR === '\\') {
    $system = false;//windows系统
}
//运行环境检测
check_env();
//运行参数处理
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
            echo "进程启动中...\r\n";
            break;
        case "stop":
            if ($system) {
                //关闭正在运行的进程
                close();
                echo "进程已关闭\r\n";
            } else {
                echo "当前环境是windows,只能在控制台运行\r\n";
            }
            $flag = false;
            break;
        case "restart":
            //重启进程
            if ($system) {
                //关闭正在运行的进程
                close();
                $daemonize = true;
                echo "进程重启中...\r\n";
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
    exit("脚本退出运行\r\n");
}
//这里有一个很诡异的问题，就是必须检查文件必须写在上面，可能是执行顺序的问题，也可能是读取文件的速度问题导致的，如果调用方法会报错
//这里必须强制检查是否已有脚本在运行，而且必须单独写在这上面，因为php执行顺序是从上到下，这里如果写方法，
//就会一直往下执行然后找对应的方法，执行了启动http服务后再检查，这个时候就会报错说端口已经被使用
if (true) {
    //检测是否正在运行，如果正在运行则不可以再开一个进程，防止修改代码后，原来的项目还在运行，导致不生效，
    $fd = fopen(__DIR__.'/lock.txt', 'w');
    //这里必须是非阻塞写入，否则进程一直挂在这里不动了
    $res = flock($fd, LOCK_EX | LOCK_NB);
    if (!$res) {
        echo $_listen."\r\n";
        echo "已有脚本正在运行，请勿重复启动，你可以使用stop停止运行或者使用restart重启\r\n";
        exit(0);
    }
}

//运行程序
if ($daemonize) {
    daemon();
} else {
    echo $_listen."\r\n";
    echo "进程启动完成,你可以按ctrl+c停止运行\r\n";
    nginx();
}
//业务逻辑代码示例，用于观测脚本是否正在运行，具体业务逻辑自己实现
function say()
{
    global $log_file;
    file_put_contents(getmypid().'_.txt',getmypid());
    while (true) {
        $fp = fopen($log_file, 'a+');
        fwrite($fp, time() . "----" . getmypid() . "\r\n");
        fclose($fp);
        sleep(2);
    }
}

//nginx服务
function nginx()
{
    require_once __DIR__.'/root/explain.php';
    $httpServer = new root\HttpServer();
    $httpServer->run();
}

//以守护进程模式运行
function daemon()
{
    ini_set('display_errors','off');
    //重设文件掩码为0，就是文件权限为0
    \umask(0);
    //创建子进程
    $pid = \pcntl_fork();
    if (-1 === $pid) {
        //创建子进程失败
        throw new Exception('Fork fail');
    } elseif ($pid > 0) {
        //必须在主进程结束前打印，否则控制台被关闭后看不到
        global $_listen;
        echo $_listen."\r\n";
        echo "进程启动完成,你可以输入php start.php stop停止运行\r\n";
        //关闭主进程
        exit(0);
    }

    global $pid_file;
    //将进程pid写入到文件当中，方便关闭进程，重启进程
    file_put_contents($pid_file, '');

    $master_pid=getmypid();
    //setsid();   //使子进程独立1.摆脱原会话控制 2.摆脱原进程组的控制 3.摆脱控制终端的控制，4，升级子进程为主进程
    if (-1 === \posix_setsid()) {
        throw new Exception("Setsid fail");
    }
    //必须在具体业务前开启多进程
    global $_server_num;
    if ($_server_num>1){
        for ($i=1;$i<=$_server_num;$i++){
            $read_log_content=file_get_contents($pid_file);
            $father=explode('-',$read_log_content);
            //去除重复的元素
            $mother=[];
            foreach ($father as $k=>$v){
                if (!array_search($v,$mother)){
                    $mother[]=$v;
                }
            }
            $worker_num=count($mother);
            if ($worker_num>=$_server_num){
                break;
            }else{
                \pcntl_fork();
                $fp=fopen($pid_file,'a+');
                fwrite($fp,getmypid().'-');
                fclose($fp);
            }
        }
    }
    //todo 这里需要给每一个进程创建一个定时任务
    root\Timer::add(2,function (){
        require_once __DIR__.'/app/timer/Test.php';
        $class=new \App\Time\Test();
        $class->handle();
    },[],true);
    root\Timer::run();

    if (getmypid()==$master_pid){
        cli_set_process_title("xiaosongshu_master");
    }else{
        cli_set_process_title("xiaosongshu_son");
    }
    //业务逻辑在子进程运行
    //many();
    nginx();
    //再次创建一个子进程，Fork再次避免系统重新控制终端
    $pid = \pcntl_fork();
    if (-1 === $pid) {
        throw new Exception("Fork fail");
    } elseif (0 !== $pid) {
        //不管是子进程还是主进程都退出
        exit(0);
    }
}

//开启多进程
function many(){
    global $pid_file;
    //将进程pid写入到文件当中，方便关闭进程，重启进程
    $fp=fopen($pid_file,'a+');
    fwrite($fp,getmypid().'-');
    fclose($fp);
    nginx();
}

//关闭运行的进程
function close()
{
    echo "关闭进程中...\r\n";
    global $pid_file;
    if (file_exists($pid_file)) {
        $master_ids = file_get_contents($pid_file);
        $master_id=explode('-',$master_ids);
        foreach ($master_id as $k=>$v){
            if ($v > 0) {
                \posix_kill($v, SIGKILL);
            }
        }
        //清空pid文件
        file_put_contents($pid_file,null);
        //等待一秒，给程序执行足够的执行时间
        sleep(1);
    }
}

//环境依赖检测
function check_env()
{
    if (!extension_loaded('sockets')) {
        exit("请先安装sockets扩展，然后开启php.ini的sockets扩展");
    }
}

//这里采用文件添加独占锁的形式，如果一个进程在后台运行，这个文件被占用了，这个脚本就不能往下执行了，只有这个进程被关闭后才会被释放
function check_run()
{
    //不仅daemon模式检查是否已经运行，
    $fd = fopen('/lock.txt', 'w');
    //这里必须是非阻塞写入，否则进程一直挂在这里不动了
    $res = flock($fd, LOCK_EX | LOCK_NB);
    if (!$res) {
        echo "已有脚本正在运行，请勿重复启动\r\n";
        exit(0);
    }
}

