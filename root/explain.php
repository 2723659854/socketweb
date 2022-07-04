<?php
namespace root;
set_time_limit(0);
//路由文件
require_once __DIR__.'/route.php';
//app解释器，根据上面的路由文件，解析出文件位置，然后加载对应的代码，执行里面的代码
require_once __DIR__.'/app.php';
//这里使用php解析请求
class HttpServer
{
    //设置监听的ip
    private $ip   = '0.0.0.0';
    //设置监听的端口
    private $port = 8020;
    //设置socket服务
    private $_socket = null;
    //初始化创建连接
    public function __construct()
    {   //创建socket连接 AF_INET：设置域名domain  SOCK_STREAM：type类型，socket流 SOL_TCP：协议类型TCP
        $this->_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        /*设置SOCKET连接的属性为SO_REUSEADDR,这样才可以端口复用*/
        socket_set_option($this->_socket,SOL_SOCKET,SO_REUSEADDR,1);
        if ($this->_socket === false) {
            die(socket_strerror(socket_last_error($this->_socket)));
        }
        global $_port;
        $this->port=$_port;
    }

    public function run()
    {
        //header("Content-Type:text/html;charset=utf-8");
        //将将连接绑定到ip和端口
        socket_bind($this->_socket, $this->ip, $this->port);
        //socket开始监听
        socket_listen($this->_socket, 5);
        //这里通过一个死循环达到常驻内存的效果
        while (true) {
            //接受socket信息流，监听连接并接受信息流
            $socketAccept = socket_accept($this->_socket);

            //读取信息流
            $request      = socket_read($socketAccept, 1024*5);
            //var_dump(($request));
            // 解析post提交的参数
            $part="form-data; name=";
            $part_length=strlen($part);
            $part_end="------WebKitFormBoundary";
            $part_end_length=strlen($part_end);
            $need_str=$request;
            $_param=[];
            while (stripos($need_str,$part)){
                $str1= substr($need_str,stripos($need_str,$part)+$part_length);
                $str2=substr($str1,0,stripos($str1,$part_end));
                $param1=array_filter(explode(PHP_EOL,$str2));
                $key=str_replace('"','',$param1[0]);
                $value=str_replace('"','',$param1[2]);

                $_param[$key]=$value;
                $str3=substr($str1,stripos($str1,$part_end)+$part_end_length);
                $need_str=$str3;
            }

            //print_r($_param);
            //向接受的文件写入响应code
            socket_write($socketAccept, 'HTTP/1.1 200 OK' . PHP_EOL);
            //写入时间
            socket_write($socketAccept, 'Date:' . date('Y-m-d H:i:s') . PHP_EOL);

            //解析用户访问的文件
            $_mark=$this->getUri($request);
            $fileName = $_mark['file'];
            $_request=$_mark['request'];
            foreach ($_mark['post_param'] as $k=>$v){
                $_param[$k]=$v;
            }
            $url=$fileName;//用户访问的路由，问号前面的是路径，后面的是参数

            //获取文件名后缀
            $fileExt  = preg_replace('/^.*\.(\w+)$/', '$1', $fileName);
            //拼接文件完整路径
            $fileName = __DIR__ . '/' . $fileName;
            switch ($fileExt) {
                case "html": //如果是html文件则直接返回文件内容
                    //set content type 设置返回文件类型
                    socket_write($socketAccept, 'Content-Type: text/html' . PHP_EOL);
                    socket_write($socketAccept, '' . PHP_EOL);
                    $fileContent = file_get_contents($fileName);
                    socket_write($socketAccept, $fileContent, strlen($fileContent));
                    break;
                case "jpg":
                case "js":
                case "css":
                case "gif":
                case "png":
                case "icon":
                case "jpeg":
                case "ico":
                    //如果是资源类文件,直接返回图片
                    socket_write($socketAccept, 'Content-Type: image/jpeg' . PHP_EOL);
                    socket_write($socketAccept, '' . PHP_EOL);
                    $fileContent = file_get_contents($fileName);
                    socket_write($socketAccept, $fileContent, strlen($fileContent));
                    break;
                default:
                    //其他类型的都默认为php类型文件，需要php文件解释
                    //非静态资源文件解析路由和参数
                    //解析get路由里面的参数
                    if (strpos($url,'?')){
                        $request_url=explode('?',$url);
                        $route=$request_url[0];
                        $params=explode('&',$request_url[1]);

                        foreach ($params as $k=>$v){
                            $_v=explode('=',$v);
                            $_param[$_v[0]]=$_v['1'];
                        }
                        $content=handle(route($route),$_param,$_request);
                    }else{
                        $content=handle(route($url),$_param,$_request);
                    }
                    socket_write($socketAccept, 'Content-Type: text/html' . PHP_EOL);
                    socket_write($socketAccept, '' . PHP_EOL);
                    socket_write($socketAccept, $content, 1024);


            }
            //socket_write($socketAccept, "\r\n welcome to  php server", 100);
            socket_close($socketAccept);

        }

    }

    public function run_test()
    {
        //header("Content-Type:text/html;charset=utf-8");
        //将将连接绑定到ip和端口
        socket_bind($this->_socket, $this->ip, $this->port);
        //socket开始监听
        socket_listen($this->_socket, 5);
        //这里通过一个死循环达到常驻内存的效果
        while (true) {
            //接受socket信息流，监听连接并接受信息流
            $socketAccept = socket_accept($this->_socket);
            socket_write($socketAccept, "\r\n welcome to  php server", 100);
            socket_close($socketAccept);
        }
    }

    //解析路由
    protected function getUri($request = '')
    {
        $arrayRequest = explode(PHP_EOL, $request);
        $line         = $arrayRequest[0];

        //这一段正则规则在windows下面生效，在Linux下不生效
        //$url         = trim(preg_replace('/(\w+)\s\/(.*)\sHTTP\/1.1/i', '$2', $line));
        //$method         = trim(preg_replace('/(\w+)\s\/(.*)\sHTTP\/1.1/i', '$1', $line));
        $str=$line.' ';
        $length=strlen($str);
        static $fuck='';
        $array=[];
        for($i=0;$i<$length;$i++){
            if (trim($str[$i])){
                $fuck=$fuck.$str[$i];
            }else{
                $array[]=$fuck;
                $fuck='';
            }
        }
        $fuck='';
        $url=$array[1];
        $method=$array[0];
        //其他的参数，都拆分成数组
        unset($arrayRequest[0]);
        foreach ($arrayRequest as $k=>$v){
            if ($v==null||$v==''){
                unset($arrayRequest[$k]);
            }
        }
        $post_param=[];
        //如果是post提交，还有可能参数在路由里面
        if ($method=='POST'||$method=='post'){
            $now=$arrayRequest;
            $param=array_pop($now);
            if (strpos($param,'&')){
                $many=explode('&',$param);
                foreach ($many as $a=>$b){
                    $dou=explode('=',$b);
                    $post_param[$dou[0]]=isset($dou[1])?$dou[1]:null;
                }
            }
        }

        $arrayRequest[]="method: ".$method;
        $arrayRequest[]="path: /".$url;
        return ['file'=>$url,'request'=>$arrayRequest,'post_param'=>$post_param];
    }

    //关闭服务
    public function close()
    {
        socket_close($this->_socket);
    }


}

//$httpServer = new HttpServer();
//$httpServer->run();
