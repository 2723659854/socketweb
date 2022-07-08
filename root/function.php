<?php
//use Redis;
function input(?string $name){
    $data=$_GET;
    if ($name){
        if (isset($data[$name])){
            return $data[$name];
        }else{
            return null;
        }
    }
}

function app_path(){
    return dirname(__DIR__);
}

function config($path_name){
    return include app_path().'/config/'.$path_name.'.php';
}

function public_path(){
    return app_path().'/public';
}

//加载控制器和所有模型,否则无法直接use使用某一个类
function traverse($path = '.')
{
    global $filePath;//得到外部定义的数组
    $current_dir = opendir($path); //opendir()返回一个目录句柄,失败返回false
    while (($file = readdir($current_dir)) !== false) { //readdir()返回打开目录句柄中的一个条目
        $sub_dir = $path . DIRECTORY_SEPARATOR . $file; //构建子目录路径
        if ($file == '.' || $file == '..') {
            continue;
        } else if (is_dir($sub_dir)) { //如果是目录,进行递归
            traverse($sub_dir); //嵌套遍历子文件夹
        } else { //如果是文件,直接输出路径和文件名
            $filePath[$path . '/' . $file] = $path . '/' . $file;//把文件路径赋值给数组
        }
    }
    return $filePath;
}

function install_base_file(){
    require_once __DIR__.'/Timer.php';
    require_once __DIR__.'/view.php';
    require_once __DIR__.'/Request.php';
    require_once __DIR__.'/BaseModel.php';
    require_once __DIR__.'/Cache.php';
    require_once __DIR__.'/queue/Queue.php';
}

//队列消费
function _queue_xiaosongshu(){
    try{
        $config=config('redis');
        $host=isset($config['host'])?$config['host']:'127.0.0.1';
        $port=isset($config['port'])?$config['port']:'6379';
        $client=new Redis();
        $client->connect($host,$port);
        while(true){
            $job=json_decode($client->RPOP('xiaosongshu_queue'),true);
            deal_job($job);
            $res=$client->zRangeByScore('xiaosongshu_delay_queue',0,time(),['limit'=>1]);
            if ($res){
                $value=$res[0];
                $res1=$client->zRem('xiaosongshu_delay_queue',$value);
                if ($res1){
                    $job=json_decode($value,true);
                    deal_job($job);
                }
            }
        }
    }catch (\Exception $exception){
        echo $exception->getMessage();
        echo "\r\n";
        echo "redis连接失败";
        echo "\r\n";
    }
}

//处理队列任务
function deal_job($job=[]){
    if (!empty($job)){
        if (class_exists($job['class'])){
            $class=new $job['class']($job['param']);
            $class->handle();
        }else{
            echo $job['class'].'不存在，队列任务执行失败！';
            echo "\r\n";
        }
    }
}

