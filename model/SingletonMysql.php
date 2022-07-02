<?php
namespace Model;
use PDO;
use PDOException;
class SingletonMysql
{
    //数据库连接必须使用单例，否则系统保存很多个连接，mysql压力变大
    private static $instance = null;
    private static $db = null;
    const DB_TYPE = 'mysql';
    const DB_HOST = '127.0.0.1';
    const DB_NAME = 'test';
    const DB_USER = 'root';
    const DB_PASS = 'root';
    const DB_MS = self::DB_TYPE . ':host=' . self::DB_HOST . ';' . 'dbname=' . self::DB_NAME;

    public $sql=null;
    public $table='user';
    protected $field='*';
    // 数据库连接
    private function __construct()
    {
        try {
            self::$db = new PDO(self::DB_MS, self::DB_USER, self::DB_PASS);
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
        return self::$db->query($sql)->fetchAll();
    }

    //first方法
    public function first()
    {
        $sql='select '.$this->field.' from '.$this->table.' '.$this->sql;

        $res=self::$db->query($sql)->fetch();
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
        $res= self::$db->query($sql)->fetchAll();

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
        //self::$db->query()->getColumnMeta()
        return self::$db->query($sql)->execute();
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
        return self::$db->query($sql)->execute();
    }

    public function delete(){

    }



}
//查询某一个列
$mysql = SingletonMysql::getInstance();
//$res=$mysql->table('user')->where('username','=','test')->field(['age','sex'])->get();
//$res=$mysql->table('user')->where('id','in',[1,2])->update(['age'=>55]);
//print_r($res);
$res2=$mysql->table('user')->insert([
    'username'=>'hanmeimei',
    'age'=>30,
    'sex'=>3,
    'create_time'=>time(),
    'update_time'=>time(),
    'status'=>1,
]);

print_r($res2);
