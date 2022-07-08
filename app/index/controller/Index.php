<?php
namespace App\Index\Controller;
use App\Model\User;
use App\Model\Book;
use Root\Request;
use Root\Cache;
use App\Queue\Test;
class Index
{
    //如果需要渲染模板就调用view 不需要渲染模板就不调用view
    public function __construct(){

    }

    public function index(){
        return view('/index/index',['time'=>date('Y-m-d H:i:s')]);
    }
    //数据查询
    public function database(Request $request){
        //print_r($request);
        $var=$request->param('var');
        $str=$request->param('str');
        $user=new User();
        $data=$user->where('username','=','test')->first();
        $app_name=config('app')['app_name'];
        return view('index/database',['var'=>$var,'str'=>date('Y-m-d H:i:s'),'user'=>json_encode($data),'app_name'=>$app_name]);
    }

    //测试数据写入
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

    //测试文件上传，以及缓存用法
    public function upload(){
        Cache::getInstance()->set('name','小松鼠');
        return view('index/upload',['cache'=>Cache::getInstance()->get('name')]);
    }

    //测试表单提交和文件上传
    public function store(Request $request){
        //var_dump($request);

        $file=$request->param('file');
        $modify=$request->param('modify');
        return view('index/say',['file'=>json_encode($file),'modify'=>$modify]);
    }
    //测试直接返回数据
    public function book(){
        //Cache::getInstance()->set('fuck','fuck you');
        //print_r(Cache::getInstance()->get('fuck'));
        return ['code'=>200,'msg'=>'ok'];
    }

    //测试接收数据并直接返回数据
    public function back_url(Request $request){
        //var_dump($request);
        return ['code'=>200,'msg'=>'ok'];
    }

    //测试队列
    public function queue(){
        Test::dispatch(['name'=>'hanmeimei','age'=>'58']);
        Test::dispatch(['name'=>'hanmeimei','age'=>'58']);
        Test::dispatch(['name'=>'hanmeimei','age'=>'58']);
        Test::dispatch(['name'=>'hanmeimei','age'=>'58']);
        Test::dispatch(['name'=>'hanmeimei','age'=>'58']);
        Test::dispatch(['name'=>'李磊','age'=>'32'],5);
        Test::dispatch(['name'=>'李磊','age'=>'32'],3);
        Test::dispatch(['name'=>'李磊','age'=>'32'],4);
        Test::dispatch(['name'=>'李磊','age'=>'32'],15);
        Test::dispatch(['name'=>'李磊','age'=>'32'],10);
        Test::dispatch(['name'=>'李磊','age'=>'32'],8);
        return '推送消息成功';
    }
}
