<?php
use Model\User;
use Model\Book;
class Index
{
    //如果需要渲染模板就调用view 不需要渲染模板就不调用view
    public function __construct(){ }
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
        print_r($data);
        $app_name=config('app')['app_name'];
        return view('index/index',['var'=>$var,'str'=>$str,'user'=>json_encode($data),'app_name'=>$app_name]);
    }

    //测试第二个方法和控制器
    public function say(){

        $book=new Book();
        $book->insert([
            'name'=>'哈利波特',
            'price'=>15.23,
            'create_time'=>time(),
            'update_time'=>time(),
        ]);
        return view('index/say');
    }
}
