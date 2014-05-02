<?php

class Pagination {

    private $tag       = 'page';
    private $total     = 0;
    private $totalpage = 0;
    private $offset    = 5;
    private $pageRows  = 10;

    private $url;
    private $paging;

    public function __construct(array $params = array()) {
        $this->init($params);
    }

    public function init( array $params = array() ) {
        if ( count($params) > 0 ) {
            foreach ($params as $key => $val) {
                $this->$key = $val;
            }
        }
    }


    
}