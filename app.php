<?php
//控制器中间件，负责根据路由加载对应的类和方法，并传入参数，返回结果
require_once './function.php';
function handle($url,$param){
    list($file,$class,$method)=explode('@',$url);
    //todo 这种都不抛出异常，而是将错误记录然后渲染到一个文件上去
    if (!file_exists($file)){
        //throw new Exception($file.'文件不存在');
        return  dispay('index',['msg'=>$file.'文件不存在']);
    }
    require_once $file;
    if (!class_exists($class)){
        //throw new Exception($class.'类不存在');
        return  dispay('index',['msg'=>$class.'类不存在']);
    }
    $class=new $class;
    if (!method_exists($class,$method)){
        //throw new Exception($method.'方法不存在');
        return  dispay('index',['msg'=>$method.'方法不存在']);
    }
    //todo 需要一个request类，然后将参数写入到request里面
    return $class->$method( $param);
}

//抛出异常
function dispay($path,$param){
    //首先是检测是否有对应的文件
    //读取其中的内容
    //根据定义的规则，在模板文件中找出变量，然后用户传的参数去替换
    $content=file_get_contents('./error/'.$path.'.html');
    if ($param){
        //搜索以{开头}结尾的字符串，然后截取出来
        $preg= '/{\$[\s\S]*?}/i';
        preg_match_all($preg,$content,$res);
        $array=$res['0'];
        //组装用户传递的变量，方便检查变脸是否合法，以及渲染变量
        $new_param=[];
        foreach ($param as $k=>$v){
            $key='{$'.$k.'}';
            $new_param[$key]=$v;
        }
        //变量检查和渲染
        foreach ($array as $k=>$v){
            if (isset($new_param[$v])){
                $content=str_replace($v,$new_param[$v],$content);
            }else{
                throw new Exception("未定义的变量".$v);
            }
        }
    }

    return $content;
}


