<?php
namespace App\Queue;
use Root\Queue\Queue;

class Test extends Queue
{
    public $param=null;
    public function __construct($param=[])
    {
        $this->param=$param;
    }

    public function handle(){
        echo "我是";
        echo $this->param['name'];echo "，今年";
        echo $this->param['age'];echo "岁。\r\n";
    }
}