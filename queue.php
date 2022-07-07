<?php
//队列
//可以通过定时器实现，生产者把任务放到定时器中，任务都是不重复的，这样子可以实现队列的效果，但是Windows无法使用，而且一旦服务关闭或者重启，就丢失了任务队列
//第二种兼容windows系统的，单独开一个进程，这个进程就是消费者，生产者将任务放到redis队列list中，消费者依次从list中取出任务执行，延迟队列就是单独放
//到有序队列zlist当中，每次取出任务，判断当前任务的执行时间是否和当前的时间一致，是则放到list队列中。
//下面是最简单的队列模型
require_once __DIR__.'/app/queue/Test.php';
$job=new \App\Queue\Test();
$job->dispatch(['name'=>'tom','age'=>17]);
$client=new Redis();
$client->connect('127.0.0.1','6379');
//生产者
$client->LPUSH('queue',json_encode(['class'=>App\Queue\Test::class,'param'=>['name'=>'tom','age'=>17]]));
//$client->LPUSH('queue',json_encode(['class'=>App\Queue\Test::class,'param'=>['name'=>'jim','age'=>15]]));
//$client->LPUSH('queue',json_encode(['class'=>App\Queue\Test::class,'param'=>['name'=>'lily','age'=>16]]));
//$client->LPUSH('queue',json_encode(['class'=>App\Queue\Test::class,'param'=>['name'=>'lucy','age'=>18]]));



while(true){

    //消费者
    $job=json_decode($client->RPOP('queue'),true);
    if (class_exists($job['class'])){
        $class=new $job['class']($job['param']);
        $class->handle();
    }else{
        echo "没有找到的任务类\r\n";
    }
    sleep(1);
}
