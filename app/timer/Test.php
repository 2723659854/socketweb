<?php
namespace App\Time;
class Test{



    public function handle(){
        file_put_contents(app_path().'/public/'.time().'book.txt','搜索');
    }
}
