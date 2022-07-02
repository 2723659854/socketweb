<?php

function input(?string $name){
    $data=$_GET;
    if ($name){
        if (isset($data[$name])){
            return $data[$name];
        }else{
            return null;
        }
    }
}

function app_path(){
    return __DIR__;
}

function config($path_name){
    return include app_path().'/config/'.$path_name.'.php';
}
