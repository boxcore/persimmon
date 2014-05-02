<?php if ( !defined('BOXCORE') ) exit('No direct script access allowed');

class TestControl extends _Control
{
    public function __construct() {
        parent::__construct();
    }

    public function index() {
        echo 'i am test';
    }
}