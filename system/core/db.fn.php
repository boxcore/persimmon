<?php if ( !defined('BOXCORE') ) exit('No direct script access allowed');

/*
  ---------------------------------------------------------------------
    MySQL数据库操作函数库  
    提供数据库连接，查询，快捷插入、修改、批量插入、修改等数据库操作函数
  
    @version 0.1
    @author boxcore
  ---------------------------------------------------------------------
*/

/**
 * 创建数据库连接
 * @param string $k 配置文件key
 * @return resource
 */
function db( $k = 'default' ) {
    $conf = conf( 'db', $k );
    if ( !$conf ) {
        trigger_error( 'Can\'t find $GLOBALS[\'db\'][\''.$k.'\'] - in database.conf.php');
    }

    $db_key = md5( "{$conf['hostname']}-{$conf['username']}-{$conf['password']}-{$conf['database']}" );
    if( ! isset( $GLOBALS[$db_key] ) ) {
        $server = $conf['hostname'] . ':' . $conf['port'];
        $link   = mysql_connect( $server, $conf['username'], $conf['password'], TRUE );
        if( !$link ) {
            trigger_error( 'Connect database be fail, Pealse inspect yours database setting!');
        }
        
        if( !mysql_select_db( $conf['database'], $link ) ) {
            trigger_error( "Can't select database - {$conf['database']}, Pealse inspect yours database setting");   
        }
        if( function_exists( 'mysql_set_charset' ) ) {
            $is_set_charset = mysql_set_charset( $conf['char_set'] );
        }
        else if( function_exists( 'mysqli_character_set_name' ) ) {
            $is_set_charset = mysqli_character_set_name( $conf['char_set'] );
        }
        else {
            $is_set_charset = mysql_query( "SET names {$conf['char_set']}" );
        }
        if( $is_set_charset != TRUE ) {
            exit( 'charset error!' );
        }
        //mysql_query( "SET names = '{$conf['char_set']}'" );
        //mysql_query("SET names utf8");
        $GLOBALS[$db_key] = $link;
    }
    
    return $GLOBALS[$db_key];
}

/**
 * 执行一条sql语句
 * @param string $sql
 * @param resource $db
 * @return bool
 */

function run_sql( $sql, $db = NULL ) {
    is_null( $db ) && $db = db();
    Logger::debug( 'run sql:' . $sql );
    $time = microtime( TRUE );
    $result = mysql_query( $sql, $db );

    if( ! isset( $GLOBALS['run_sql'] ) ) { $GLOBALS['run_sql'] = array(); }
    $GLOBALS['run_sql'][$sql] = microtime( TRUE ) - $time;
    $GLOBALS['last_sql']      = $sql;

    if( mysql_error() ) {
        trigger_error( 'MySQL error ' . mysql_errno() . mysql_error(), E_USER_ERROR );
    }
    
    // 触发执行 SQL 钩子
    run_func_coll( 'run_sql_after' );
    return $result;
}

/**
 * 转义字符串
 * @param mixed
 * @return mixed
 */
function s( $str, $db = NULL ) {
    is_null( $db ) && $db = db();
    return mysql_real_escape_string( $str, $db );
}

/**
 * 格式化sql
 * @param $str
 * @return string
 */
function prepare( $sql, $array, $db = NULL ) {
    if( !is_array( $array ) ) {
        $array = func_get_args();
        $sql   = array_shift( $args );
    }
    
    $sql = str_replace( array( '?i', '?s' ), array( '%d', '"%s"' ), $sql );
    foreach( $array as $k => $v ) {
        $array[$k] = s( $v, $db );
    }
    return vsprintf( $sql, $array );
}

/**
 * 获取多行记录
 * @param $sql
 * @return array
 */
function get_data( $sql, $db = NULL ) {
    $result = run_sql( $sql, $db );
    $data = array();
    while(1) {
        $array = mysql_fetch_assoc($result);
        if(!$array) { break; }
        $data[] = $array;
    }
    mysql_free_result( $result );
    return !empty( $data ) ? $data : FALSE;
}


/**
 * 获取一行记录
 * @param $sql
 * @return array
 */
function get_line( $sql, $db = NULL ) {
    $result = run_sql( $sql, $db );
    $data = mysql_fetch_assoc($result);
    mysql_free_result($result);
    return $data;
}

/**
 * 快捷获取一行记录
 * @param $table
 * @param $value
 * @param $index
 * @return array
 */
function quick_line( $table, $value, $index = 'id', $db = NULL ) {
    //$sql = prepare( "SELECT * FROM `{$table}` WHERE `$index` = ?s LIMIT 1", $value );
    $value = prepare( '?s', array( $value ), $db );
    $sql = "SELECT * FROM `{$table}` WHERE `$index` = $value LIMIT 1";
    return get_line( $sql, $db );
}

/**
 * 快速检索
 * @param $table
 * @param $where
 * @param $select
 * @param $db
 * @return mixed
 */
function quick_get( $table, $wheres, $select = '*', $db = NULL ) {
    $get_where = array();
    if( ! empty( $wheres ) ) {
        foreach( $wheres as $field => $value ) {
            array_push( $get_where, sprintf( '`%s` = "%s"', $field, s( $value, $db ) ) );
        }
        $get_where = 'WHERE ' . implode( ' AND ', $get_where );
    } else {
        $get_where = '';
    }
    return get_line( "SELECT {$select} FROM `{$table}` {$get_where}", $db );
}

/**
 * 快捷插入数据
 * @param $table
 * @param $data
 */
function insert( $table, $data, $db = NULL ) {
    is_null( $db ) && $db = db();
    run_sql( insert_sql( $table, $data, $db ), $db );
    return mysql_insert_id( $db );
}

/**
 * 快捷批量插入数据
 * @param $table
 * @param $data
 */
function insert_batch( $table, $data, $db = NULL ) {
    is_null( $db ) && $db = db();
    $field_arr = array();
    $value_sql_arr = array();
    $first = true;
    foreach( $data as $d ) {
        $value_arr = array();
        foreach( $d as $field=>$value ) {
            if( $first )  {
                $field_arr[] = $field;
            }
            $value_arr[] = sprintf( '"%s"', s( $value, $db ) );         
        }       
        $value_sql_arr[] = '(' . implode( ',', $value_arr ) . ')';  
        $first = false;
    }
    
    $fields_sql = '`' . implode( '`, `', $field_arr ) . '`';
    $values_sql = implode( ',', $value_sql_arr );
    $sql = "INSERT INTO `{$table}` ( {$fields_sql} ) VALUES {$values_sql}";
    return run_sql( $sql, $db );
}

/**
 * 快捷更新表
 * @param $table
 * @param $data
 * @param $wheres
 */
function update( $table, $data, $wheres = array(), $db = NULL ) {
    is_null( $db ) && $db = db();
    $update_data  = array();
    $update_where = array();
    foreach( $data as $field => $value ) {
        array_push( $update_data, sprintf( '`%s` = "%s"', $field, s( $value, $db ) ) );
    }
    $update_data  = implode( ', ', $update_data );
    
    if( ! empty( $wheres ) ) {
        foreach( $wheres as $field => $value ) {
            array_push( $update_where, sprintf( '`%s` = "%s"', $field, s( $value, $db ) ) );
        }
        $update_where = 'WHERE ' . implode( ' AND ', $update_where );
    } else {
        $update_where = '';
    }
    $update = run_sql( "UPDATE `{$table}` SET {$update_data} {$update_where}", $db );
    return $update;
}

/**
 * 快捷删除表
 * @param string $table
 * @param string $wheres
 * @param $db
 * @return bool
 */
function delete( $table, $wheres, $db = NULL ) {
    is_null( $db ) && $db = db();
    $delete_where = array();
    if( ! empty( $wheres ) ) {
        foreach( $wheres as $field => $value ) {
            array_push( $delete_where, sprintf( '`%s` = "%s"', $field, s( $value, $db ) ) );
        }
        $delete_where = 'WHERE ' . implode( ' AND ', $delete_where );
    } else {
        $delete_where = '';
    }
    $delete = run_sql( "DELETE FROM `$table` {$delete_where}", $db );
    return $delete;
}

/**
 * 获取快捷插入数据sql
 * @param $table
 * @param $data
 */
function insert_sql( $table, $data, $db = NULL ) {
    $insert_fileds = array();
    $insert_data   = array();
    foreach( $data as $field => $value ) {
        array_push( $insert_fileds, "`{$field}`" );
        array_push( $insert_data, sprintf( '"%s"', s( $value, $db ) ) );
    }
    $insert_fileds = implode( ', ', $insert_fileds );
    $insert_data   = implode( ', ', $insert_data );
    return "INSERT INTO `{$table}` ({$insert_fileds}) values ({$insert_data})";
}

/**
 * 以变量方式获取记录
 * @param $sql
 * @return mixed
 */
function get_var( $sql, $db = NULL ) {
    $data = get_line( $sql, $db );
    return ! is_array( $data ) ? NULL : @array_shift( $data );
}

/**
 * 获取上次INSERT_ID
 * @param $db
 * @return number
 */
function last_id( $db = NULL ) {
    return get_var( 'SELECT LAST_INSERT_ID() ', $db );
}

/**
 * 获取DB错误编号
 * @return number
 */
function db_errno( $db = NULL ) {
    is_null( $db ) && $db = db();
    return mysql_errno( $db );
}

/**
 * 获取DB错误消息
 * @return string
 */
function db_error( $db = NULL ) {
    is_null( $db ) && $db = db();
    return mysql_error( $db );
}

/**
 * 关闭数据库连接
 * @param $db
 */
function close_db( $db = 'default' ) {

    if( is_string( $db ) ) {

        $conf = conf( 'db', $db );

        if ( ! empty( $conf ) ) {

            $db_key = md5( "{$conf['hostname']}-{$conf['username']}-{$conf['password']}-{$conf['database']}" );

            if( ! empty( $GLOBALS[$db_key] ) && is_resource( $GLOBALS[$db_key] ) ) {

                mysql_close( $GLOBALS[$db_key] );
                unset( $GLOBALS[$db_key] );
            }
        }
    }
    elseif( is_resource( $db ) ) {

        mysql_close( $db );
    }
}