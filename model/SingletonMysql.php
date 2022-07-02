<?php
namespace Model;
use PDO;
use PDOException;
trait SingletonMysql
{
    //数据库连接必须使用单例，否则系统保存很多个连接，mysql压力变大
    private static $instance = null;
    private static $db = null;
//    const DB_TYPE = 'mysql';
//    const DB_HOST = '127.0.0.1';
//    const DB_NAME = 'test';
//    const DB_USER = 'root';
//    const DB_PASS = 'root';
//    const DB_MS = self::DB_TYPE . ':host=' . self::DB_HOST . ';' . 'dbname=' . self::DB_NAME;

    private $type = 'mysql';
    private $host = '127.0.0.1';
    private $db_name = 'test';
    private $user = 'root';
    private $pass = 'root';


    private $sql=null;
    public $table=null;
    protected $field='*';
    // 数据库连接
    private function __construct()
    {
        try {
            self::$db = new PDO($this->type.':host=' . $this->host . ';' . 'dbname=' . $this->db_name, $this->user, $this->pass);
            self::$db->query('set names utf8mb4');
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('error:' . $e->getMessage());
        }
    }

    // 禁止clone
    private function __clone()
    {

    }

    //获取单例
    public static function getInstance(): object
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    //设置查询的字段
    public function field(array $field){
        $this->field=implode(',',$field);
        return $this;
    }

    //原生sql查询
    public function query(string $sql = ''): array
    {
        try{
            return self::$db->query($sql)->fetchAll();
        }catch (PDOException $exception){
            die("Error!: " . $exception->getMessage() . "<br/>");
        }

    }

    //first方法
    public function first()
    {
        $sql='select '.$this->field.' from '.$this->table.' '.$this->sql;


        try{
            $res=self::$db->query($sql)->fetch();
        }catch (PDOException $exception){
            die("Error!: " . $exception->getMessage() . "<br/>");
        }
        foreach ($res as $k=>&$v){
            if (is_numeric($k)){
                unset($res[$k]);
            }
        }
        return $res;

    }

    //get 方法
    public function get()
    {
        $sql='select '.$this->field.' from '.$this->table.' '.$this->sql;
        try{
            $res= self::$db->query($sql)->fetchAll();
        }catch (PDOException $exception){
            die("Error!: " . $exception->getMessage() . "<br/>");
        }
        foreach ($res as $k=>$v){
            foreach ($v as $m=>$n){
                if (is_numeric($m)){
                    unset($res[$k][$m]);
                }
            }
        }
        return $res;
    }

    //where方法
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

    //update方法
    public function update(array $param){
        $_param=[];
        foreach ($param as $k=>$v){
            $_param[]=$k.' = '.$v;
        }
        $sql='update '.$this->table.' SET '.implode(',',$_param).$this->sql;
        try{
            return self::$db->query($sql)->execute();
        }catch (PDOException $exception){
            die("Error!: " . $exception->getMessage() . "<br/>");
        }

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

        try{
            return self::$db->query($sql)->execute();
        }catch (PDOException $exception){
            die("Error!: " . $exception->getMessage() . "<br/>");
        }

    }

    //删除
    public function delete(){
        $sql='delete from '.$this->table.' '.$this->sql;
        try{
            return self::$db->query($sql)->execute();
        }catch (PDOException $exception){
            die("Error!: " . $exception->getMessage() . "<br/>");
        }

    }
}

