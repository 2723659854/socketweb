<?php


class Mysql
{
    protected $mysql = null;
    protected    $list  = null;
    protected $sql='';
    public $value=null;


    //初始化数据库连接
    public function __construct()
    {
        global $mysql;
        $mysqli = new mysqli("localhost", "root", "root", "test", '3306');
        $mysqli->set_charset('utf8');
        $this->mysql = $mysqli;
        $mysql = $mysqli;

    }




    //first查询
    public function first()
    {
        return $this->list->fetch_assoc();

    }

    //get 查询
    public function get($sql)
    {
        $this->list=$this->mysql->query($sql);
        $data = [];
        if ($this->list) {
            while ($myrow = mysqli_fetch_row($this->list)) {
                $data[]=$myrow;
            }
        }
        return $data;
    }



    public function __call($name, $args)
    {
        $this->value = call_user_func($name, $this->value, $args[0]);
        return $this;
    }

    public function say(){
        return strlen($this->value);
    }

}

function where($mysql){

    global $mysql;
    $mysql->query('select * from user');
}

$mysql =new Mysql();
$sql='select * from user';
//$res=$mysql->where($sql)->fetch_assoc();
$res=$mysql->trim('0')->first();
print_r($res);