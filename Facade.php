<?php

abstract class Facade
{
    //这个是用来存已经被实例的单例
    protected static $instances = [];

    /**
     * @return string
     */
    protected static function getAccessor()
    {
        throw new RuntimeException("请设置facade类");
    }

    //2，单利方法，通过类名获取单例
    public static function getInstance()
    {
        //获取类名称
        $name = static::getAccessor();      // 注意此处用的是static而非self  是因为static是静态继承后
        if (!isset(static::$instances[$name])) {
            //如果不存在，就new一个，并保存到静态单例数组中
            static::$instances[$name] = new $name();
        }
        //然后返回
        return static::$instances[$name];
    }

    //1，首先调用这个魔术方法，当调用不存在的静态方法的时候就会调用这个方法
    public static function __callStatic($method, $arguments)
    {
        //通过单利方法或者这一个类，
        $instance = static::getInstance();
        //然后调用这一个类的的方法，传入参数
        return $instance->$method(...$arguments);
    }
}

/**
 * 继承了facade类，
 * 这个a1类作用是绑定a2类
 * Class a2
 * @method static void f1()
 */
class a1 extends Facade
{

    protected static function getAccessor()
    {
        return 'a2';
    }
}

/**
 * 实际操作类
 * Class a2
 */
class a2
{
    public function f1($param)
    {
        echo $param;
    }
}

a1::f1(1111);