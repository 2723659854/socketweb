<?php
namespace APP\Facade;
use root\Facade;
class User extends Facade
{
    protected static function getAccessor()
    {
        return 'App\Model\User';
    }
}