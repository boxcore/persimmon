<?php if ( !defined('BOXCORE') ) exit('No direct script access allowed');

echo dirname(__FILE__);
class IndexControl
{
    public function __construct() {
        echo 1234;
    }

    public function index() {
        echo 'i am index';
    }
}