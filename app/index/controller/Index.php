<?php
namespace App\Index\Controller;
use App\Model\User;
use App\Model\Book;
use Root\Request;
class Index
{
    //如果需要渲染模板就调用view 不需要渲染模板就不调用view
    public function __construct(){

    }
    //控制器里面写了一个index方法
    public function index(Request $request){
        //print_r($request);
        $var=$request->param('var');
        $str=$request->param('str');
        $user=new User();
        $data=$user->where('username','=','test')->first();
        $app_name=config('app')['app_name'];
        return view('index/index',['var'=>$var,'str'=>date('Y-m-d H:i:s'),'user'=>json_encode($data),'app_name'=>$app_name]);
    }

    //测试第二个方法和控制器
    public function say(Request $request){

        //var_dump($request);
        $book=new Book();
        $book->insert([
            'name'=>'哈利波特',
            'price'=>15.23,
            'create_time'=>time(),
            'update_time'=>time(),
        ]);
        return view('index/say');
    }

    public function upload(){
        return view('index/upload');
    }

    public function store(Request $request){
        //echo "store\r\n";
        print_r($request);
        return view('index/say');
    }

    public function book(){
        echo 234234;
        return '333';
    }
}
