<?php


class DelayQueue
{

    public  $redis=null;

    public function __construct()
    {
        $redis=new Redis();
        $redis->connect('127.0.0.1',6379);
        $this->redis=$redis;
    }

    //将任务添加到队列中
    //todo 可能存在同一时间点，两个用户存放了相同的任务，需要区分，这个时候就在每一个事件上加一个参数，随机数
    //这里和普通队列合并的时候，加一个延迟时间参数，如果有则调用zadd，否则调用lpush
    public function set($key,$value,$score){

        //将任务添加到 key队列， 规则：NX不更新存在的成员   分数   值
        //必须保证value不一样
        $res=$this->redis->zAdd($key,['NX'],$score,$value);
    }

    //任务处理
    public function deal($key){
        while(true){
            //返回有序集合key  分数从0到当前时间 只返回一个  有序集合是从小到大排序的，所以返回的是最小值
            $res=$this->redis->zRangeByScore($key,0,time(),['limit'=>1]);
            //todo 跟普通队列合并的时候，不需要这一段代码 ,同时获取两个集合当中的值，没有睡眠，
            /*if (empty($res)){//如果没有，则暂停一秒
                sleep(1);
                continue;
            }*/
            if ($res){
                //如果有任务，则只处理一个
                $value=$res[0];
                //移除队列key 当中的成员value,
                $res1=$this->redis->zRem($key,$value);
                if ($res1){
                    echo "------------处理\r\n";
                    var_dump($value);
                    echo "------------处理\r\n";
                }
            }
        }
    }
}

$model=new DelayQueue();
$key='fuck';
$model->set($key,1,time()+5);
$model->set($key,2,time()+5);
$model->set($key,3,time()+10);
$model->set($key,4,time()+8);
$model->deal($key);
