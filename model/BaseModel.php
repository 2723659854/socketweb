<?php

namespace Model;

use mysqli;

class BaseModel
{
    public $mysql;
    public $sql;
    public $table = 'user';
    protected $host = null;
    protected $username = null;
    protected $password = null;
    protected $dbname = null;
    protected $port = null;
    protected $type = 'mysql';


    public function __construct($str = null)
    {
//        $this->value = $str;
//        $mysqli = new mysqli("127.0.0.1", "root", "root", "test", '3306');
//        $mysqli->set_charset('utf8');
//        $this->mysql = $mysqli;
//        $this->sql=$this->sql."select * from ".$this->table;
        $config = config('database');
        $this->type = $config['default'];
        $database_config = $config[$this->type];
        $this->host = $database_config['host'];
        $this->username = $database_config['username'];
        $this->password = $database_config['passwd'];
        $this->dbname = $database_config['dbname'];
        $this->port = $database_config['port'];

        $mysqli = new mysqli($this->host, $this->username, $this->password, $this->dbname, $this->port);
        //
        //$mysqli = new mysqli('127.0.0.1', 'root', 'root', 'test', '3306');

        $mysqli->set_charset('utf8');
        $this->mysql = $mysqli;
        $this->sql = $this->sql . "select * from " . $this->table;
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

    public function first()
    {
        return $this->mysql->query($this->sql)->fetch_assoc();
    }

    public function get()
    {
        $list = $this->mysql->query($this->sql);
        $data = [];
        if ($list) {
            while ($myrow = mysqli_fetch_row($list)) {
                $data[] = $myrow;
            }
        }
        return $data;
    }

    public function where($name, $logic, $value)
    {
        $this->sql = $this->sql . ' where `' . $name . '` ' . $logic . ' "' . $value . '"';
        return $this;
    }

    public function insert()
    {

    }

    public function save()
    {

    }

    public function update()
    {

    }

    public function delete()
    {

    }

    public function whereIn()
    {

    }

    //关闭连接
    public function close()
    {
        //$this->mysql->close();
    }
}

$class = new BaseModel(' SSF');
//echo $class->trim('0')->strlen();

//每一次的查询都必须单独实例化
//可执行的语句first方法
$data = $class->where('username', '=', 'test')->first();
print_r($data);
//测试get方法
//$data2=$class->where('id','>=',1)->get();
//print_r($data2);
