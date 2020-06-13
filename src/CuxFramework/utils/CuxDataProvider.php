<?php

/**
 * CuxDataProvider abstract class file
 * 
 * @package Utils
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\utils;

use CuxFramework\utils\Cux;

/**
 * Abstract class that serves as a base-ground for future work.
 * Use this as a starting point to render list data structures
 */
abstract class CuxDataProvider {
    
    /**
     * How many records to display for each page
     */
    const ROWS_PER_PAGE = 10;
    
    /**
     * Render this before the actual list rendering
     * @var string
     */
    private $_header = "";
    
    /**
     * Render this after the actual list rendering
     * @var string
     */
    private $_footer = "";
    
    /**
     * The list of columns to be shown based on the provided data list
     * @var array
     */
    private $_columns = array();
    
    /**
     * CSS class for the table that displays the list data
     * @var string
     */
    private $_tableClass = "table table-striped table-hover table-fixed-layout";
    
    /**
     * CSS class for the table header
     * @var string
     */
    private $_tHeadClass ="thead-light";
    
    /**
     * Flag that is used to render the data filters
     * @var bool
     */
    private $_showFilter = false;
    
    /**
     * Flag that is used to check whether the rendering should be done for mobile devices
     * @var bool
     */
    private $_isMobile = false;
    
    /**
     * Abstract function that needs to be implemented by extending classes.
     * Here you should process the list data and display the requested informations
     * @return string
     */
    abstract function render(): string;
    
    /**
     * Class constructor.
     * Use the $options parameters to setup class properties:
     *     "header" => $this->_header ( string )
     *     "footer" => $this->_footer ( string )
     *     "columns" => $this->_columns ( array )
     *     "tableClass" => $this->_tableClass ( string )
     *     "tHeadClass" => $this->tHeadClass ( string )
     *     "isMobile" => $this->_isMobile ( bool )
     *     "showFilter" => $this->_showFilter ( bool )
     * @param array $options
     */
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
    
    /**
     *  Setter for the $_isMobile property
     * @param bool $isMobile
     */
    public function setIsMobile(bool $isMobile){
        $this->_isMobile = $isMobile;
    }
    
    /**
     * Getter for the $_isMobile property
     * @return bool
     */
    public function getIsMobile(): bool{
        return $this->_isMobile;
    }
    
    /**
     * Setter for the $_showFilter property
     * @param bool $showFilter
     */
    public function setShowFilter(bool $showFilter){
        $this->_showFilter = $showFilter;
    }
    
    /**
     * Getter for the $_showFilter property
     * @return bool
     */
    public function getShowFilter(): bool{
        return $this->_showFilter;
    }
    
    /**
     * Setter for the $_header property
     * @param string $header
     */
    public function setHeader(string $header){
        $this->_header = $header;
    }
    
    /**
     * Getter for the $_header property
     * @return string
     */
    public function getHeader(): string{
        return $this->_header;
    }
    
    
    /**
     * Setter for the $_footer property
     * @param string $footer
     */
    public function setFooter(string $footer){
        $this->_footer = $footer;
    }
    
    /**
     * Getter for the $_footer property
     * @return string
     */
    public function getFooter(): string{
        return $this->_footer;
    }
    
    /**
     * Setter for the $_columns property
     * @param array $columns
     */
    public function setColumns(array $columns = null){
        if (!is_null($columns)){
            $this->_columns = $columns;
        }
    }
    
    /**
     * Getter for the $_columns property
     * @return string
     */
    public function getColumns(): array{
        return $this->_columns;
    }
    
    /**
     * Setter for the $_tableClass property
     * @param string $tableClass
     */
    public function setTableClass(string $tableClass){
        $this->_tableClass = $tableClass;
    }
    
    /**
     * Getter for the $_tableClass property
     * @return string
     */
    public function getTableClass(): string{
        return $this->_tableClass;
    }
    
    /**
     * Setter for the $_tHeadClass property
     * @param string $theadClass
     */
    public function setTHeadClass(string $theadClass){
        $this->_tHeadClass = $theadClass;
    }
    
    /**
     * Getter for the $_tHeadClass property
     * @return string
     */
    public function getTHeadClass(): string{
        return $this->_tHeadClass;
    }
    
}