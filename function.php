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
