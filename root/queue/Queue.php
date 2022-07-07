<?php

namespace Root\Queue;
use Redis;
class Queue
{
    public static function name(){
        return get_called_class();
        //get_class
    }

    public function handle(){}

    //队列生产者
    public static function  dispatch($param=[]){
        $config=config('redis');
        $host=isset($config['host'])?$config['host']:'127.0.0.1';
        $port=isset($config['port'])?$config['port']:'6379';
        $client=new Redis();
        $client->connect($host,$port);
        //$class=__CLASS__;
        $class=self::name();
        $client->LPUSH('queue',json_encode(['class'=>$class,'param'=>$param]));
        $client->close();
    }



}