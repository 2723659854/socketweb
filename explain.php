<?php
set_time_limit(0);
//这里使用php解析请求
class HttpServer
{
    private $ip   = '0.0.0.0';
    private $port = 8001;

    private $_socket = null;

    public function __construct()
    {
        $this->_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->_socket === false) {
            die(socket_strerror(socket_last_error($this->_socket)));
        }
    }

    public function run()
    {
        socket_bind($this->_socket, $this->ip, $this->port);
        socket_listen($this->_socket, 5);
        while (true) {
            $socketAccept = socket_accept($this->_socket);
            $request      = socket_read($socketAccept, 1024);
            echo $request;
            socket_write($socketAccept, 'HTTP/1.1 200 OK' . PHP_EOL);
            socket_write($socketAccept, 'Date:' . date('Y-m-d H:i:s') . PHP_EOL);

            $fileName = $this->getUri($request);
            $fileExt  = preg_replace('/^.*\.(\w+)$/', '$1', $fileName);
            $fileName = __DIR__ . '/' . $fileName;
            switch ($fileExt) {
                case "html":
                    //set content type
                    socket_write($socketAccept, 'Content-Type: text/html' . PHP_EOL);
                    socket_write($socketAccept, '' . PHP_EOL);
                    $fileContent = file_get_contents($fileName);
                    socket_write($socketAccept, $fileContent, strlen($fileContent));
                    break;
                case "jpg":
                    socket_write($socketAccept, 'Content-Type: image/jpeg' . PHP_EOL);
                    socket_write($socketAccept, '' . PHP_EOL);
                    $fileContent = file_get_contents($fileName);
                    socket_write($socketAccept, $fileContent, strlen($fileContent));
                    break;
                default:
                    //其他类型的都默认为php类型文件，需要php文件解释
                    socket_write($socketAccept, 'Content-Type: text/html' . PHP_EOL);
                    socket_write($socketAccept, '' . PHP_EOL);
                    //todo  这个test.php 文件 可以写入路由文件，
                    //路由文件
                    require_once './route.php';
                    //app解释器，根据上面的路由文件，解析出文件位置，然后加载对应的代码，执行里面的代码
                    require_once './app.php';
                    //将结果返回给str
                    //假设用户传递了两个值 a=6,b=9,计算结果后返回
                    $a=600;
                    $b=950;
                    $str=plus($a,$b);
                    //todo 首先要定义一个模板变量识别规则，就是比如使用{{作为变量开始符号，使用 }}作为变量结束标识符，然后使用正则匹配，找到HTML文件中的变量，然后将计算结果替换变量，生成的最终的
                    //todo 内容作为html内容返回给浏览器
                    //todo 如果是借口，则直接返回字符串，字符串都是转化为json格式
                    socket_write($socketAccept, $str, 1024);

            }
            socket_write($socketAccept, "\r\n欢迎使用php自定义服务", 100);
            socket_close($socketAccept);

        }

    }

    protected function getUri($request = '')
    {
        $arrayRequest = explode(PHP_EOL, $request);
        $line         = $arrayRequest[0];
        $file         = trim(preg_replace('/(\w+)\s\/(.*)\sHTTP\/1.1/i', '$2', $line));
        return $file;
    }


    public function close()
    {
        socket_close($this->_socket);
    }


}

$httpServer = new HttpServer();
$httpServer->run();
