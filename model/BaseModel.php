<?php

namespace Model;

use mysqli;
use mysqli_sql_exception;
class BaseModel
{

    protected $host = null;
    protected $username = null;
    protected $password = null;
    protected $dbname = null;
    protected $port = null;
    protected $type = 'mysql';

    public $mysql;
    public $sql;
    public $table = 'user';
    public $field='*';


    public function __construct()
    {
//        $config = config('database');
//        $this->type = $config['default'];
//        $database_config = $config[$this->type];
//        $this->host = $database_config['host'];
//        $this->username = $database_config['username'];
//        $this->password = $database_config['passwd'];
//        $this->dbname = $database_config['dbname'];
//        $this->port = $database_config['port'];

        //$mysqli = new mysqli($this->host, $this->username, $this->password, $this->dbname, $this->port);
        $mysqli = new mysqli('127.0.0.1', 'root', 'root', 'test', '3306');
        $mysqli->set_charset('utf8');
        $this->mysql = $mysqli;
        $this->sql = '';
    }


    //下面是数据库的链式操作

    public function first()
    {
        $sql='select '.$this->field.' from '.$this->table.' '.$this->sql;

        return $this->mysql->query($sql)->fetch_assoc();

    }

    public function get()
    {
        $sql='select '.$this->field.' from '.$this->table.' '.$this->sql;
        $list = $this->mysql->query($sql);
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
        $int=false;
        if (is_array($value)){
            foreach ($value as $v){
                if (is_numeric($v)){
                    $int=true;
                    break;
                }
            }
            $value='('.implode(',',$value).')';
        }else{
            if (is_numeric($value)){
                $int=true;
            }
        }
        if ($int){
            $str= ' ' . $value . ' ';
        }else{
            $str=' "' . $value . '"';
        }
        $this->sql = $this->sql . '  where `' . $name . '` ' . $logic .$str;
        return  $this;
    }

    //table 方法
    public function table($name){
        $this->table=$name;
        return $this;
    }
    //设置查询的字段
    public function field(array $field){
        $this->field=implode(',',$field);
        return $this;
    }

    //写入
    public function insert(array $param){
        $key=[];
        $val=[];
        foreach ($param as $k=>$v){
            $key[]=$k;
            if (is_string($v)){
                $v='"'.$v.'"';
            }
            $val[]=$v;
        }
        $sql="insert into "."$this->table  (".implode(',',$key).") values(".implode(',',$val).")";
        return $this->mysql->query($sql);
    }

    //update方法
    public function update(array $param){
        $_param=[];
        foreach ($param as $k=>$v){
            if (is_string($v)){
                $v='"'.$v.'"';
            }
            $_param[]=$k.' = '.$v;
        }
        $sql='update '.$this->table.' SET '.implode(',',$_param).$this->sql;
        return $this->mysql->query($sql);

    }


    //删除
    public function delete(){
        $sql='delete from '.$this->table.' '.$this->sql;
        return $this->mysql->query($sql);
    }
}

//$mysql = new BaseModel();
//$res=$mysql->table('user')->where('uername','=','test')->first();
//$res=$mysql->table('book')->insert([
//    'name'=>'哈利波特',
//    'price'=>15.23,
//    'create_time'=>time(),
//    'update_time'=>time(),
//]);
//$res=$mysql->table('book')->where('id','=',1)->update(['name'=>'小朋友']);
//$res=$mysql->table('book')->where('id','=',1)->delete();
//print_r($res);
