<?php

//变脸的界定符,只能是单括号或者多括号{}或者{{}}，
$rule=[
    'start'=>"{",
    'end'=>"}",
];
//这里是解析试图文件，并渲染变量到模板,变量必须是数组的形式
function view($path,$param=[]){
    //首先是检测是否有对应的文件
    //读取其中的内容
    //根据定义的规则，在模板文件中找出变量，然后用户传的参数去替换
    $content=file_get_contents(app_path().'/view/'.$path.'.html');
    if ($param){
        //搜索以{开头}结尾的字符串，然后截取出来
        $preg= '/{\$[\s\S]*?}/i';
        preg_match_all($preg,$content,$res);
        $array=$res['0'];
        //组装用户传递的变量，方便检查变脸是否合法，以及渲染变量
        $new_param=[];
        foreach ($param as $k=>$v){
            $key='{$'.$k.'}';
            $new_param[$key]=$v;
        }
        //变量检查和渲染
        foreach ($array as $k=>$v){
            if (array_key_exists($v,$new_param)){
                if ($new_param[$v]==null){
                    $new_param[$v]='';
                }
                $content=str_replace($v,$new_param[$v],$content);
            }else{
                //throw new Exception("未定义的变量".$v);
               return no_declear('index',['msg'=>"未定义的变量".$v]);
            }
        }
    }

    return $content;
}
function no_declear($path,$param){
    //首先是检测是否有对应的文件
    //读取其中的内容
    //根据定义的规则，在模板文件中找出变量，然后用户传的参数去替换
    $content=file_get_contents(app_path().'/error/'.$path.'.html');
    if ($param){
        //搜索以{开头}结尾的字符串，然后截取出来
        $preg= '/{\$[\s\S]*?}/i';
        preg_match_all($preg,$content,$res);
        $array=$res['0'];
        //组装用户传递的变量，方便检查变脸是否合法，以及渲染变量
        $new_param=[];
        foreach ($param as $k=>$v){
            $key='{$'.$k.'}';
            $new_param[$key]=$v;
        }
        //变量检查和渲染
        foreach ($array as $k=>$v){
            if (isset($new_param[$v])){
                $content=str_replace($v,$new_param[$v],$content);
            }else{
                throw new Exception("未定义的变量".$v);
            }
        }
    }

    return $content;
}
//view('index',['var'=>"尼玛"]);
