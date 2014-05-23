<?php if ( !defined('BOXCORE') ) exit('No direct script access allowed');

class IndexControl extends _Control
{
    public function __construct() {
    }

    public function index() {
        print_r($GLOBALS['request']);
        echo '<hr>';
        echo 'i am index';
    }
}