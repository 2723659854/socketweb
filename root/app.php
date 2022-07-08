<?php
//控制器中间件，负责根据路由加载对应的类和方法，并传入参数，返回结果
require_once __DIR__.'/function.php';
require_once __DIR__.'/view.php';
require_once __DIR__.'/Request.php';
//require_once __DIR__.'/BaseModel.php';
//require_once __DIR__.'/Cache.php';
//require_once __DIR__ . '/Timer.php';
//加载所有用户定义的文件
//foreach (traverse(app_path().'/app') as $key => $val) {
//    require_once $val;
//}

function handle($url, $param,$_request)
{

    list($file, $class, $method) = explode('@', $url);
    $file=app_path().$file;
    //var_dump($file);
    if (!file_exists($file)) {
        //echo "文件不存在\r\n";
        //throw new Exception($file.'文件不存在');
        return dispay('index', ['msg' => $file . '文件不存在']);
    }
    require_once $file;
    //$class='App\\Controller\\'.$class;
    //$class='App\\'.'Index'.'\\Controller'.$class;
    if (!class_exists($class)) {
        //echo "类不存在\r\n";
        //throw new Exception($class.'类不存在');
        return dispay('index', ['msg' => $class . '类不存在']);
    }
    $class = new $class;

    if (!method_exists($class, $method)) {
        //echo "{{$method}}方法不存在\r\n";
        //throw new Exception($method.'方法不存在');
        return dispay('index', ['msg' => $method . '方法不存在']);
    }
    //处理用户请求参数
    global $fuck;
    $fuck=new Root\Request();
    foreach ($_request as $k=>$v){
        $v=trim($v);
        if ($v){
            $_pos=strripos($v,": ");
            $key=substr($v,0,$_pos);
            $value=substr($v,$_pos+1,strlen($v));
            if ($key){
                $fuck->header($key,$value);
            }
        }
    }
    foreach ($param as $k=>$v){
        $fuck->set($k,$v);
    }
    //todo 工厂模式
    //处理用户请求
    $response=$class->$method($fuck);
    //返回响应结果
    if ($fuck->_error){
        return $fuck->_error;
    }else{
        return $response;
    }

}

//抛出异常
function dispay($path, $param)
{
    //首先是检测是否有对应的文件
    //读取其中的内容
    //根据定义的规则，在模板文件中找出变量，然后用户传的参数去替换
    $content = file_get_contents(app_path().'/root/error/' . $path . '.html');
    if ($param) {
        //搜索以{开头}结尾的字符串，然后截取出来
        $preg = '/{\$[\s\S]*?}/i';
        preg_match_all($preg, $content, $res);
        $array = $res['0'];
        //组装用户传递的变量，方便检查变脸是否合法，以及渲染变量
        $new_param = [];
        foreach ($param as $k => $v) {
            $key = '{$' . $k . '}';
            $new_param[$key] = $v;
        }
        //变量检查和渲染
        foreach ($array as $k => $v) {
            if (isset($new_param[$v])) {
                $content = str_replace($v, $new_param[$v], $content);
            } else {
                throw new Exception("未定义的变量" . $v);
            }
        }
    }

    return $content;
}


