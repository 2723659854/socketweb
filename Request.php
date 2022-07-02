<?php


class Request
{

    public $value=['name'=>'tome'];

    public function param($name=''){
        if ($name){
            if (isset($this->value[$name])){
                return $this->value[$name];
            }else{
                return null;
            }
        }else{
            return $this->value;
        }
    }

}
