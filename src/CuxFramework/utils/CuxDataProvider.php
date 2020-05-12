<?php

namespace CuxFramework\utils;

use CuxFramework\utils\Cux;

abstract class CuxDataProvider {
    
    const ROWS_PER_PAGE = 10;
    
    private $_header = "";
    private $_footer = "";
    private $_columns = array();
    private $_tableClass = "table table-striped table-hover table-fixed-layout";
    private $_tHeadClass ="thead-light";
    
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
        
        if (isset($options["tableClass"])){
            $this->setTableClass($options["tableClass"]);
        }
        
        if (isset($options["tHeadClass"])){
            $this->setTHeadClass($options["tHeadClass"]);
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
    
    public function setTableClass(string $tableClass){
        $this->_tableClass = $tableClass;
    }
    
    public function getTableClass(): string{
        return $this->_tableClass;
    }
    
    public function setTHeadClass(string $theadClass){
        $this->_tHeadClass = $theadClass;
    }
    
    public function getTHeadClass(): string{
        return $this->_tHeadClass;
    }
    
}