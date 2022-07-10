<?php

namespace APP\Facade;

use root\Facade;

//门面类，方便静态调用其他类
class User extends Facade
{
    protected static function getAccessor()
    {
        return 'App\Model\User';
    }
}
