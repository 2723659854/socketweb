<?php
namespace root;
set_time_limit(0);
//路由文件
require_once __DIR__.'/route.php';
//app解释器，根据上面的路由文件，解析出文件位置，然后加载对应的代码，执行里面的代码
require_once __DIR__.'/app.php';
if (file_exists(dirname(__DIR__).'/vendor/autoload.php')){
    require_once dirname(__DIR__).'/vendor/autoload.php';
}
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
        @\socket_set_option($this->_socket,SOL_SOCKET,SO_REUSEADDR,1);
        @\socket_set_option($this->_socket,SOL_SOCKET,SO_REUSEPORT,1);
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
            $request      = socket_read($socketAccept, 1024*1000);
            //echo $request;
            $_param=[];
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
            //处理路由当中的/
            //$fileName=implode('/',array_filter(explode('/',$fileName)));
            //获取文件名后缀
            $fileExt  = preg_replace('/^.*\.(\w+)$/', '$1', $fileName);
            //拼接文件完整路径
            switch ($fileExt) {
                case "html": //如果是html文件则直接返回文件内容
                    //set content type 设置返回文件类型
                    socket_write($socketAccept, 'Content-Type: text/html' . PHP_EOL);
                    socket_write($socketAccept, '' . PHP_EOL);
                    $fileName = dirname(__DIR__) . '/view/' . $fileName;
                    if (file_exists($fileName)){
                        $fileContent = file_get_contents($fileName);
                    }else{
                        $fileContent='sorry,the file is missing!';
                    }
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
                    $fileName = dirname(__DIR__) . '/public/' . $fileName;
                    if (file_exists($fileName)){
                        $fileContent = file_get_contents($fileName);
                    }else{
                        $fileContent='sorry,the file is missing!';
                    }

                    socket_write($socketAccept, $fileContent, strlen($fileContent));
                    break;
                default:
                    //todo linux 会丢失数据,真鸡儿难搞
                    //其他类型的都默认为php类型文件，需要php文件解释
                    //非静态资源文件解析路由和参数
                    //解析get路由里面的参数
                    if (($url)&&strpos($url,'?')){
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

                    if ($content){
                        $content=is_string($content)?$content:json_encode($content);
                        $write_length=strlen($content);
                        if ($write_length<1024){
                            $write_length=1024;
                        }
                    }else{
                        $write_length=1024;
                    }
                    socket_write($socketAccept, $content,$write_length );


            }
            //socket_write($socketAccept, "\r\n welcome to  php server", 100);
            socket_close($socketAccept);

        }

    }

    public function run123()
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
            $request      = socket_read($socketAccept, 1024);
            var_dump($request);
            socket_write($socketAccept, "\r\n welcome to  php server", 100);
            socket_close($socketAccept);
        }
    }

    //解析路由
    protected function getUri($request = '')
    {
        //var_dump($request);
        $arrayRequest = explode(PHP_EOL, $request);
        $line         = $arrayRequest[0];

        //var_dump($arrayRequest);
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
        if (isset($array[1])){
            $url=$array[1];
        }else{
            $url='/';
        }
        if (isset($array[0])){
            $method=$array[0];
        }else{
            $method='GET';
        }

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
            foreach ($now as $a=>$b){

                if (stripos($b,'form-data; name="')){

                    $str1=substr($b,stripos($b,'form-data; name="'));
                    $arr=explode('"',$str1);
                    //var_dump($arr);
                    $key=$arr[1];
                    $value=isset($now[$a+2])?$now[$a+2]:null;
                    $post_param[$key]=$value;

                    if (stripos($b,'; filename="')){
                        $str1=substr($b,stripos($b,'; filename="'));
                        $arr=explode('"',$str1);
                        $_filename=$arr[1];
                        $_filecontent=isset($now[$a+3])?$now[$a+3]:null;
                        $post_param['file']=['filename'=>$_filename,'content'=>$_filecontent];
                        $post_param[$key]=['filename'=>$_filename,'content'=>$_filecontent];
                    }
                }
                /*if (stripos($b,'; filename="')){
                    $str1=substr($b,stripos($b,'; filename="'));
                    $arr=explode('"',$str1);
                    $_filename=$arr[1];
                    $_filecontent=isset($now[$a+3])?$now[$a+3]:null;
                    $post_param['file']=['filename'=>$_filename,'content'=>$_filecontent];
                }*/
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
