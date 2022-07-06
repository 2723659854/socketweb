<?php


namespace App\Time;


class OtherTimer
{

    public function handel(){
        file_put_contents(app_path().'/public/book/'.time().'note.txt','搜索');
    }
}
