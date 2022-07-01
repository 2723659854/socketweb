<?php


interface Model
{

    public function __construct();

    public function where($name,$logic,$value);

    public function first();

    public function get();
}
