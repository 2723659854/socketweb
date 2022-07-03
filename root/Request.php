<?php
namespace Root;

class Request
{

    public $value=['name'=>'tome'];

    public $_error=null;

    /**
     * 获取request请求参数
     * @param string $name
     * @return string|string[]|null
     */
    public function param($name=''){
        if ($name){
            if (isset($this->value[$name])){
                return $this->value[$name];
            }elseif(isset($this->value['header'][$name])){
                return $this->value['header'][$name];
            }else{
                return null;
            }
        }else{
            return $this->value;
        }
    }

    /**
     * 设置请求参数
     * @param $key
     * @param $value
     */
    public function set($key,$value){
        $this->value[$key]=$value;
    }

}
