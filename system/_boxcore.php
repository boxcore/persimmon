<?php

/*
  ----------------------------------------------------------------------
    boxcore框架引导程序
    加载应用程序配置、基础类库，实现整个框架流程
    
    @pack boxcore
    @version 0.1 (presimmon)
    @link http://framework.boxcore.org
    @author chunze.huang
  ----------------------------------------------------------------------
*/

// 分隔符
define( 'DS', DIRECTORY_SEPARATOR );

// 项目根目录路径
define( 'BOXCORE', dirname(__FILE__).DS);

// 设置当前请求语言，默认设置为简体中文
$GLOBALS['request']['lang'] = 'zh_cn';

// 载入应用程序配置
require ROOT . 'conf'.DS.'config.php';
$GLOBALS['boxcore'] = isset($_CONFIGS) ? isset($_CONFIGS) : array();

// 载入日志类
require BOXCORE . 'core'.DS.'Logger.lib.php';

// 载入框架核心函数库
require BOXCORE . 'core'.DS.'core.fn.php';

// 载入框架数据库操作函数
require BOXCORE . 'core'.DS.'db.fn.php';

// 载入程序全局函数（程序公用函数库）
require ROOT . 'funcs'.DS.'app.fn.php';

// 取消自动转义
transcribe();

// 如果用户没有设置site_domain，则自动配置生成site_domain
if( !$site_domain = conf( 'app', 'site_domain' ) ) {
    $GLOBALS['app']['site_domain'] = gen_site_domain();
}

// 获取当前请求URL
$GLOBALS['request']['url'] = get_current_url();

// 获取请求URI
$GLOBALS['request']['uri'] = get_current_uri();

// 解析URI，如果不存在则响应404
if( ! parse_uri( $GLOBALS['request']['uri'] ) ) {
    show_404();
}

