<?php

namespace CuxFramework\utils;

use CuxFramework\utils\Cux;

abstract class CuxDataProvider {
    
    const ROWS_PER_PAGE = 10;
    
    private $_header = "";
    private $_footer = "";
    private $_columns = array();
    
    abstract function render(): string;
    
    public function __construct(array $options = array()){
        
        if (isset($options["header"])){
            $this->setHeader($options["header"]);
        }
        
        if (isset($options["footer"])){
            $this->setFooter($options["footer"]);
        }
        
        if (isset($options["columns"])){
            $this->setColumns($options["columns"]);
        }
        
    }
    
    public function setHeader(string $header){
        $this->_header = $header;
    }
    
    public function getHeader(): string{
        return $this->_header;
    }
    
    public function setFooter(string $footer){
        $this->_footer = $footer;
    }
    
    public function getFooter(): string{
        return $this->_footer;
    }
    
    public function setColumns(array $columns = null){
        if (!is_null($columns)){
            $this->_columns = $columns;
        }
    }
    
    public function getColumns(): array{
        return $this->_columns;
    }
    
}