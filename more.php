<?php

abstract class Facade
{
    protected static $instances = [];

    /**
     * @return string
     */
    protected static function getAccessor()
    {
        throw new RuntimeException("请设置facade类");
    }

    public static function getInstance()
    {
        $name = static::getAccessor();      // 注意此处用的是static而非self  是因为static是静态继承后
        if (!isset(static::$instances[$name])) {
            static::$instances[$name] = new $name();
        }
        return static::$instances[$name];
    }

    public static function __callStatic($method, $arguments)
    {
        $instance = static::getInstance();
        return $instance->$method(...$arguments);
    }
}

/**
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