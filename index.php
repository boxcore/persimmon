<?php
// boxcore 入口文件

// 定义产品环境 以及设置报错方式
define('ENVIRONMENT', 'development');

if (defined('ENVIRONMENT'))
{
    switch (ENVIRONMENT)
    {
        case 'development':
            error_reporting(E_ALL);
        break;
    
        case 'testing':
        case 'production':
            error_reporting(0);
        break;

        default:
            exit('The application environment is not set correctly.');
    }
}

// 指定应用相关目录
$system_path = 'system';
$app_path = 'site';

