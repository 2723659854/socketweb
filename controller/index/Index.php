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
        //$user=User::getInstance();
        $data=$user->where('username','=','test')->first();

        $app_name=config('app')['app_name'];

        return view('index/index',['var'=>$var,'str'=>$str,'user'=>json_encode($data),'app_name'=>$app_name]);
    }
}
