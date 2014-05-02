<?php if ( !defined('BOXCORE') ) exit('No direct script access allowed');

/**
 * 日志记录类
 * @author boxcore
 *
 */
class Logger {
    
    // 默认日志文件大小
    private static $filesize = 1048576;

    /**
     * debug log
     * @param string $msg
     */
    public static function debug( $msg ) {
        if( ENV == 'development' ) {
            self::__output( 'debug', $msg );
        }
    }
    
    /**
     * info log
     * @param string $msg
     */
    public static function info( $msg ) {
        if( ENV == 'test' || ENV == 'development' ) {
            self::__output( 'info', $msg );
        }
    }
    
    /**
     * error log
     * @param string $msg
     */
    public static function error( $msg ) {
        self::__output( 'error', $msg );
    }
    
    /**
     * 输出日志
     * @param string $level 日志级别
     * @param string $msg 消息
     */
    private static function __output( $level, $msg ) {
        $output = isset( $GLOBALS['boxcore']['log_output'] ) ? $GLOBALS['boxcore']['log_output'] : 'none';
        
        // 组装日志信息
        $msg = date('Y-m-d H:i:s') . " [{$level}] {$msg}\r\n";
        
        if( 'file' == $output ) {
            self::__output4file($level, $msg);
        }
        elseif( 'database' == $output ) {
            self::__output4database($level, $msg);
        }
        elseif( 'console' == $output ) {
            self::__output4console($level, $msg);
        }
        else {
            return;
        }
    }
    
    /**
     * 输出到文件
     * @param string $level
     * @param string $msg
     */
    private static function __output4file( $level, $msg ) {
        $log_file = self::__getOutputFile();
        if ( ! $fp = @fopen($log_file, 'a')) {
            return false;
        }
        flock($fp, LOCK_EX);
        fwrite($fp, $msg);
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }
    
    /**
     * 输出到Eclipse控制台
     * @param string $level
     * @param string $msg
     */
    private static function __output4console( $level, $msg ) {
        $service_port = '8281';  // socket port
        $address = '127.0.0.1';  // socket server ip
                  
        // create socket object
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);  
        if ($socket === false) {  
            trigger_error( 'Creating socket failed:' . socket_strerror(socket_last_error()) );
        } 
                  
        // create socket connet
        $result = socket_connect($socket, $address, $service_port);  
        if ($result === false) {
            trigger_error( 'Socket connect failed:' . socket_strerror(socket_last_error($socket)) );
        } 
        
        $msg .= 'END';  // add stop string
               
        socket_write($socket, $msg, strlen($msg));  
        socket_close($socket);  
    }
    
    /**
     * 输出到数据库
     * @param string $level
     * @param string $msg
     */
    private static function __output4database( $level, $msg ) {
        $data = array(
            'level' => $level,
            'msg'   => $msg
        );
        insert( 'log', $data );
    }
    
    /**
     * 获取输出文件
     * @return string
     */
    private static function __getOutputFile() {
        $time = time();
        $last_file = 0; 
        
        // 获取日志输出目录，如果目录不存在，创建目录
        $output_dir = ROOT . conf( 'xxoo', 'logs_dir' ) .DS. date('Y', $time). DS .date('m', $time). DS .date('d', $time);
        mkdirs( $output_dir );

        // 获取当前应输出的日志文件
        $handle = opendir($output_dir); 
        while ( false !== ($filename = readdir($handle)) ) {
            if($filename != '.' && $filename != '..') {
                list($filename, $ext) = explode('.', $filename);
                if( $filename > $last_file ) {
                    $last_file = $filename;
                }
            }
        }
        closedir($handle);
        $output_file =  $output_dir . DS . $last_file . '.log';
        
        // 如果文件存在，判断文件是否到达最大限制，如果到达最大限制创建一个新文件
        if( file_exists($output_file) && filesize( $output_file ) > self::$filesize ) {
            $output_file =  $output_dir . DS . ($last_file+1) . '.log';
        }

        return $output_file;
    }
    
    
}