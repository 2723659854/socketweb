<?php
function app_path(){
    return dirname(__DIR__);
}

function config($path_name){
    return include app_path().'/config/'.$path_name.'.php';
}

function public_path(){
    return app_path().'/public';
}


function traverse($path = '.')
{
    global $filePath;
    $current_dir = opendir($path);
    while (($file = readdir($current_dir)) !== false) {
        $sub_dir = $path . DIRECTORY_SEPARATOR . $file;
        if ($file == '.' || $file == '..') {
            continue;
        } else if (is_dir($sub_dir)) {
            traverse($sub_dir);
        } else {
            $filePath[$path . '/' . $file] = $path . '/' . $file;
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
    require_once __DIR__.'/Facade.php';
}


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
            if (empty($job)&&empty($res)){
                sleep(1);
            }
        }
    }catch (\Exception $exception){
        echo $exception->getMessage();
        echo "\r\n";
        echo "redis连接失败";
        echo "\r\n";
    }
}


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

