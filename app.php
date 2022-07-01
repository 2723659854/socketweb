<?php
//控制器中间件，负责根据路由加载对应的类和方法，并传入参数，返回结果
function handle($url,$param){
    list($file,$class,$method)=explode('@',$url);
    //todo 这种都不抛出异常，而是将错误记录然后渲染到一个文件上去
    if (!file_exists($file)){
        throw new Exception($file.'文件不存在');
    }
    require_once $file;
    if (!class_exists($class)){
        throw new Exception($class.'类不存在');
    }
    $class=new $class;
    if (!method_exists($class,$method)){
        throw new Exception($method.'方法不存在');
    }
    return $class->$method($param);
}
