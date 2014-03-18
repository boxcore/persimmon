<?php if ( !defined('BOXCORE') ) exit('No direct script access allowed');

/*
  ----------------------------------------------------------------------
	框架引导程序
	加载应用程序配置、基础类库，实现整个框架流程
	
	@pack BOXCORE
	@version
	@link
	@author
  ----------------------------------------------------------------------
*/

// 载入日志类 
require BOXCORE . 'core'.DS.'Logger.lib.php';

// 设置当前请求语言，默认设置为简体中文
$GLOBALS['request']['lang'] = 'zh_cn';

// 载入框架基本配置
require NEST . 'conf'.DS.'app.conf.php';

// 载入应用程序配置
require ROOT . 'conf'.DS.'app.conf.php';

// 载入框架数据库配置
require NEST . 'conf'.DS.'database.conf.php';

// 载入程序数据库配置
$myConfig = ROOT . 'conf'.DS.'database.conf.php';
if( file_exists($myConfig) ){ include $myConfig; }


// 载入框架核心函数库
require BOXCORE . 'core'.DS.'core.fn.php';

// 载入框架数据库操作函数
require BOXCORE . 'core'.DS.'db.fn.php';

// 载入框架全局函数（系统公用函数库）
require NEST . 'funcs'.DS.'app.fn.php';

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

// 挂载钩子
mount_hooks( $GLOBALS['request']['uri'] );

/**
 * base controller
 */
abstract class _Control { public function __construct() {} }

// Controller预处理
$__cont_file = ROOT . $GLOBALS['request']['file'];
if( !is_file( $__cont_file ) ) trigger_error( "Can\'t find controller file: {$__cont_file}", E_USER_ERROR );
require( $__cont_file );

$__class_name = $GLOBALS['request']['class'];

if( !class_exists( $__class_name ) ) trigger_error( "Can\'t find class: {$__class_name}", E_USER_ERROR );
$__BOXCORE = new $__class_name();

$__method = $GLOBALS['request']['fn'];
if( !method_exists( $__BOXCORE, $__method ) ) trigger_error( "Can\'t find method: {$__method}", E_USER_ERROR );

$__params = isset($GLOBALS['request']['params']) ? $GLOBALS['request']['params'] : array();

// 执行action 前置钩子
run_func_coll( 'pre_control' );

// 执行action()方法
call_user_func_array( array($__BOXCORE, $__method), $__params );

// 执行action 后置钩子
run_func_coll( 'post_control' );

exit;