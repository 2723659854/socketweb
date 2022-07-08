<?php


namespace APP\Facade;


use root\Facade;

class Cache extends Facade
{

    protected static function getAccessor()
    {
        return 'Root\Cache';
    }

}