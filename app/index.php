<?php

/*
  ---------------------------------------------------------------------------
    boxcore框架入口

    @version 0.1
    @link http://framework.boxcore.org
  ---------------------------------------------------------------------------
*/

// 定义应用目录
define('ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);

// 载入框架引导文件
require '../system/_boxcore.php';

print_r($GLOBALS['request']);