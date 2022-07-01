<?php
use Model\User;
class Index
{
    //如果需要渲染模板就调用view 不需要渲染模板就不调用view
    public function __construct(){

        input('name');
    }
    //控制器里面写了一个index方法
    public function index($param){
        if (isset($param['var'])){
            $var=$param['var'];
        }else{
            $var="哈哈哈，模板渲染成功";
        }
        if (isset($param['str'])){
            $str=$param['str'];
        }else{
            $str='say hello';
        }
        $user=new User();
        $data=$user->where('username','=','test')->first();

        return view('index/index',['var'=>$var,'str'=>$str,'user'=>json_encode($data)]);
    }
}
