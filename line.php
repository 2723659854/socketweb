<?php
class Fuck
{
    public $value;
    public $mysql;
    public $list;
    public $sql;
    public $table;


    public function __construct($str=null)
    {
        $this->value = $str;
        $mysqli = new mysqli("localhost", "root", "root", "test", '3306');
        $mysqli->set_charset('utf8');
        $this->mysql = $mysqli;
    }

    public function trim($t)
    {
        $this->value = trim($this->value, $t);
        return $this;
    }

    public function strlen()
    {
        return strlen($this->value);
    }


    //下面是数据库的链式操作

    public function first(){
        return $this->mysql->query($this->sql)->fetch_assoc();
    }

    public function get(){
        $list=$this->mysql->query($this->sql);
        $data = [];
        if ($list) {
            while ($myrow = mysqli_fetch_row($list)) {
                $data[]=$myrow;
            }
        }
        return $data;
    }

    public function where($name,$logic,$value){
        $this->sql=$this->sql.' where `'.$name.'` '.$logic.' "'.$value.'"';
        return $this;
    }

    public function table($name){
        $this->sql=$this->sql.' select * from '.$name.' ';
        return $this;
    }

    public function insert(){

    }

    public function save(){

    }

    public function update(){

    }

    public function delete(){

    }

    public function whereIn(){

    }
}

$class=new Fuck(' SSF');
//echo $class->trim('0')->strlen();

//每一次的查询都必须单独实例化
//可执行的语句first方法
$data=$class->table('user')
    ->where('username','=','test')
    ->first();
print_r($data);
//测试get方法
$data2=$class->table('user')->where('id','>',1)->get();
print_r($data2);
