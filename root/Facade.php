<?php
namespace root;
use RuntimeException;
//门面抽象类
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
    private static function getInstance()
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