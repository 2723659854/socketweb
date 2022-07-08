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
    public static function  dispatch($param=[],$delay=0){
        $config=config('redis');
        $host=isset($config['host'])?$config['host']:'127.0.0.1';
        $port=isset($config['port'])?$config['port']:'6379';
        $client=new Redis();
        $client->connect($host,$port);
        //$class=__CLASS__;
        $class=self::name();
        if($delay>0){
            $client->zAdd('xiaosongshu_delay_queue',['NX'],time()+$delay,json_encode(['class'=>$class,'param'=>$param]));
        }else{
            $client->LPUSH('xiaosongshu_queue',json_encode(['class'=>$class,'param'=>$param]));
        }
        $client->close();
    }



}