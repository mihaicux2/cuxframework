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
    
    private $_showFilter = false;
    
    private $_isMobile = false;
    
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
        
        if (isset($options["isMobile"]) && is_bool($options["isMobile"])){
            $this->setIsMobile($options["isMobile"]);
        }
        
        if (isset($options["showFilter"]) && is_bool($options["showFilter"])){
            $this->setShowFilter($options["showFilter"]);
        }
        
    }
    
    public function setIsMobile(bool $isMobile){
        $this->_isMobile = $isMobile;
    }
    
    public function getIsMobile(): bool{
        return $this->_isMobile;
    }
    
    public function setShowFilter(bool $showFilter){
        $this->_showFilter = $showFilter;
    }
    
    public function getShowFilter(): bool{
        return $this->_showFilter;
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