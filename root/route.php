<?php
//todo 这里面写路由解析方法，根据路由解析出需要加载的文件的路径
//定义路由解析规则
//todo 这里就要求类文件和类名必须大写
function route($url){
    //首先拆分路由
    $url=array_filter(explode('/',$url));
    $new_url=[];
    foreach ($url as $k=>$v){
        $new_url[]=$v;
    }
    $num=count($new_url);
    switch ($num){
        case 0:
            //没有路径直接访问根路径的index模块的index控制器的index方法
            return './app/index/controller/Index.php@APP\\Index\\Controller\\Index@index';
            break;
        case 1:
            //没有路径直接访问根路径的index模块的index控制器的index方法
            return './app/index/controller/Index.php@App\\Index\\Controller\\Index@'.$new_url[0];
            break;
        case 2:
            //没有路径直接访问根路径的index模块的index控制器的index方法
            return './app/index/controller/'.ucwords($new_url[0]).'.php@'.'App\\Index\\Controller\\'.ucwords($new_url[0]).'@'.$new_url[1];
            break;
        case 3:
            //没有路径直接访问根路径的index模块的index控制器的index方法
            return './app/'.strtolower($new_url[0]).'/controller/'.ucwords($new_url[1]).'.php@'.'App\\'.ucwords($new_url[0]).'\\Controller\\'.ucwords($new_url[1]).'@'.$new_url[2];
            break;
        default:
            return './app/index/controller/Index.php@APP\\Index\\Controller\\Index@index';
    }
}
