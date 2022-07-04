<?php

namespace Root;

use mysqli;
use mysqli_sql_exception as MysqlException;
use Root\Request;
class BaseModel
{

    private $host = null;
    private $username = null;
    private $password = null;
    private $dbname = null;
    private $port = null;
    private $type = 'mysql';

    private $mysql;
    private $sql;
    public $table = 'user';
    private $field='*';
    private $order='id asc';
    private $limit=0;
    private $offset=0;



    public function __construct()
    {
//        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
//        $config = config('database');
//        $this->type = $config['default'];
//        $database_config = $config[$this->type];
//        $this->host = $database_config['host'];
//        $this->username = $database_config['username'];
//        $this->password = $database_config['passwd'];
//        $this->dbname = $database_config['dbname'];
//        $this->port = $database_config['port'];


        try{
            //$mysqli = new mysqli($this->host, $this->username, $this->password, $this->dbname, $this->port);
            $mysqli = new mysqli('122.51.99.152', 'root', 'root', 'test', '3306');
        }catch (MysqlException $e){
            echo $e->getMessage();
            die("数据库连接失败！");
        }
        //$mysqli = new mysqli('127.0.0.1', 'root', 'root', 'test', '3306');
        $mysqli->set_charset('utf8');
        $this->mysql = $mysqli;
        $this->sql = '';
    }


    //下面是数据库的链式操作

    /**
     * 单条数据查询
     * @return array|null
     */
    public function first()
    {
        if ($this->limit){
            $limit=' limit ' .$this->offset.' ,'.$this->limit;
        }else{
            $limit='';
        }
        $sql='select '.$this->field.' from '.$this->table.' where '.$this->sql.' order by '.$this->order.$limit;

        try{
            return $this->mysql->query($sql)->fetch_assoc();
        }catch (MysqlException $e){
            global $fuck;
            $fuck->_error=no_declear('index',['msg'=>"错误码：".$e->getCode()."<br>文件：".$e->getFile()."<br>行数：".$e->getLine().PHP_EOL."<br>错误详情：".$e->getMessage()]);
            return [];
        }
    }

    /**
     * 多条数据查询
     * @return array
     */
    public function get()
    {
        if ($this->limit){
            $limit=' limit ' .$this->offset.' ,'.$this->limit;
        }else{
            $limit='';
        }
        $sql='select '.$this->field.' from '.$this->table.' where '.$this->sql.' order by '.$this->order.$limit;
        try{
            $list = $this->mysql->query($sql);
            $data = [];
            //返回键值对对象
            while($row=$list->fetch_object())
            {
                $array=[];
                foreach ($row as $k=>$v){
                    $array[$k]=$v;
                }
                $data[]=$array;
            }
            return $data;
        }catch (MysqlException $e){

            echo $e->getMessage();
            die("数据库操作失败！");
        }

    }


    /**
     * 查询条件
     * @param string $name 字段
     * @param string $logic 逻辑
     * @param string|array $value 值
     * @return $this
     */
    public function where(string $name, string $logic,  $value)
    {
        if ($this->sql){
            $this->sql=$this->sql.' and ';
        }
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
        $this->sql = $this->sql . ' `' . $name . '` ' . $logic .$str;
        return  $this;
    }

    //table 方法

    /**
     * 设置表名
     * @param string $name = tableName
     * @return $this
     */
    public function table(string $name){
        $this->table=$name;
        return $this;
    }
    //设置查询的字段

    /**
     * 指定查询字段
     * @param array $field =[field1,field2...]
     * @return $this
     */
    public function field(array $field){
        $this->field=implode(',',$field);
        return $this;
    }

    //写入

    /**
     * 插入
     * @param array $param=[key1=>value1,key1=>value1,]
     * @return bool|\mysqli_result
     */
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
        try{
            return $this->mysql->query($sql);
        }catch (MysqlException $e){

            echo $e->getMessage();
            die('数据库操作失败');
        }

    }

    //update方法

    /**
     * 更新
     * @param array $param =[key1=>value1,key2=>value2]
     * @return bool|\mysqli_result
     */
    public function update(array $param){
        $_param=[];
        foreach ($param as $k=>$v){
            if (is_string($v)){
                $v='"'.$v.'"';
            }
            $_param[]=$k.' = '.$v;
        }
        $sql='update '.$this->table.' SET '.implode(',',$_param).$this->sql;
        try{
            return $this->mysql->query($sql);
        }catch (MysqlException $e){

            echo $e->getMessage();
            die('数据库操作失败');
        }
    }


    //删除

    /**
     * 删除
     * @return bool|\mysqli_result
     */
    public function delete(){
        $sql='delete from '.$this->table.' '.$this->sql;
        try{
            return $this->mysql->query($sql);
        }catch (MysqlException $e){

            echo $e->getMessage();
            die('数据库操作失败');
        }
    }

    //排序

    /**
     * 排序
     * @param string $field 排序字段
     * @param string $order 排序类型 asc 升序 desc 降序
     * @return $this
     */
    public function order($field='id',$order='asc'){
        $this->order=$field.' '.$order;
        return $this;
    }

    //分页

    /**
     * 分页
     * @param int $limit 查询条数
     * @param int $offset 偏移量
     * @return $this
     */
    public function limit($limit=0,$offset=0){

        $this->offset=$offset;
        $this->limit=$limit;
        return $this;
    }
}

