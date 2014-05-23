<?php if ( !defined('BOXCORE') ) exit('No direct script access allowed');

require BOXCORE.'libs/Phpxcel.lib.php';

/**
 * Export导出 Demo
 *
 * @author boxcore
 * @date 2014-05-23
 */

class ExportControl extends _Control
{
    public function __construct() {
        parent::__construct();
    }

    public function index() {
        print_r($GLOBALS['request']);
        echo 'i am export';
    }

    /**
     * 导出Excel
     *
     * @return bool
     */
    public function exportExcel() {

        $sql = 'SELECT * FROM `table` WHERE TRUE and source_ip like "广东省%" and created >= "2014-01-01 00:00:00" and created < "2014-02-01 00:00:00"';
        $data = get_data($sql);


        /**
         * 执行导出操作
         */
        if( !empty($data) ){
            $field = array(
                'id' => 'id',
                'author' => '名字',
                'address' => '地址',
                'source_ip' => 'ip',
                'mobile' => '电话',
                'created' => '创建时间',
            );

            $this->__sendTrailFile('导出记录.xls', $data, $field);

        }else{
            echo '没有需要导出的文件';
        }

    }


    /**
     * 生成excel文件
     *
     * @param $filename
     * @param $data 需要导出的数据
     * @param $field excel中对于列名
     */
    private function __sendTrailFile($filename, $data, $field) {
        $phpxcel = new PHPExcel();
        $writer  = new PHPExcel_Writer_Excel5($phpxcel);

        $sheet_index = 0;

        /**
         * Sheet 1
         */
        if($data){
            if($sheet_index){
                $phpxcel->createSheet($sheet_index);
                $phpxcel->setActiveSheetIndex($sheet_index);
            }else{
                $phpxcel->setActiveSheetIndex($sheet_index);
            }

            $phpxcel->getActiveSheet()->setTitle('my sheet');
            $cid = array('A','B','C','D','E', 'F', 'G','H','I','J','K','L');

            $i=0;$j=1;
            foreach($field as $k=>$v){
                $phpxcel->getActiveSheet()->setCellValue($cid[$i].$j, $field[$k]);
                $i++;
            }

            foreach ($data as $k=>$v) {
                $i=0;++$j;
                foreach($field as $k2=>$v2){
                    $phpxcel->getActiveSheet()->setCellValue( $cid[$i].$j, $v[$k2]);
                    $i++;
                }
                
            }

            // $phpxcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
            // $phpxcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            // $phpxcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            // $phpxcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
            // $phpxcel->getActiveSheet()->getColumnDimension('G')->setWidth(40);
            // $phpxcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);

            ++$sheet_index;
        }

        $phpxcel->setActiveSheetIndex(0);

        /** 导出excel */
        header('Content-Type: application/vnd.ms-excel');


        // 设置头文件
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $encoded_filename = str_replace("+", "%20", urlencode($filename));
        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }

        header('Cache-Control: max-age=0');

        $writer->save('php://output'); // 输出到浏览器
    }

}