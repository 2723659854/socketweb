<?php
require_once './view.php';
class Index
{

    public function __construct(){

    }
    //控制器里面写了一个index方法
    public function index($param){
        echo "我是控制器\r\n";
        //如果需要渲染模板就调用view
        //不需要渲染模板就不调用view
        return view('index',['var'=>$param['a']+$param['b']]);
    }
}