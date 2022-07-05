<?php
$path = __DIR__;
$config= include $path . '/config/server.php';
$port=isset($config['upload'])?$config['upload']:10008;
$fp = fsockopen("127.0.0.1", $port, $errno, $errstr, 10);
$array = $_FILES['file'];
if (!$fp) {
    echo "open fail\r\n";
} else {
    if (!empty($array)) {
        $str = 'filename:' . $array['name'];
        fwrite($fp, $str);
        $filename = $array['tmp_name'];
        $handle = fopen($filename, "r");
        $contents = fread($handle, filesize($filename));
        fwrite($fp, $contents);
    }
    echo "上传文件成功";
}

fclose($fp);

?>
