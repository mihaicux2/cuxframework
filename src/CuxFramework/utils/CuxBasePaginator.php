<?php

/**
 * CuxBasePaginator abstract class file
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
 * Use this as a starting point to render data paginators
 */
abstract class CuxBasePaginator {

    /**
     * Flag to tell whether the "first" and "last" links should be rendered
     * @var bool
     */
    public $showFirstAndLast = true;
    
    /**
     * How many buttons ( pages ) to display
     * @var int
     */
    public $maxPagesDisplayed = 10;
    
    /**
     * The current page
     * @var int
     */
    protected $_page = 1;
    
    /**
     * How many pages there are in total
     * @var int
     */
    protected $_totalPages = 1;
    
    /**
     * How many results are in total
     * @var int
     */
    protected $_totalResults = 0;
    
    /**
     * The base path for ( to be- ) generated page links
     * @var string
     */
    protected $_basePath;
    
    /**
     * The GET parameter used to generate links and to determine the current page
     * @var string
     */
    protected $_pageParam = "page";
    
    /**
     * The list of HTML buttons to be rendered
     * @var array
     */
    protected $_texts = array();

    /**
     * Abstract class that needs to be implemented by extending classes.
     * Use this for the actual rendering of the paginator
     * @return string
     */
    public abstract function render(): string;
    
    /**
     * Class constructor
     * @param int $page - current page
     * @param int $totalPages - total pages
     * @param string $basePath - base path for ( to be- ) generated links
     */
    public function __construct(int $page, int $totalPages, string $basePath = "") {
        
        $this->setPage($page);
        $this->setTotalPages($totalPages);
        $this->setBasePath($basePath);
        
        if ($this->maxPagesDisplayed < 7){
            $this->maxPagesDisplayed = 7;
        }
        
        $this->_texts = $this->processTexts();
        
    }
    /**
     * Setter for the $_pageParam property
     * @param string $pageParam
     */
    public function setPageParam(string $pageParam){
        $this->_pageParam = $pageParam;
    }
    
    /**
     * Getter for the $_pageParam property
     * @return string
     */
    public function getPageParam(): string{
        return $this->_pageParam;
    }
    
    /**
     * Setter for the $_page property
     * @param int $page
     */
    public function setPage(int $page) {
        if ($page < 1){
            $page = 1;
        }
        $this->_page = $page;
        
        $this->_texts = $this->processTexts();
    }
    
    /**
     * Getter for the $_page property
     * @return int
     */
    public function getPage(): int {
        return $this->_page;
    }
    
    /**
     * Setter for the $_totalResults property
     * @param int $totalResults
     */
    public function setTotalResults(int $totalResults){
        if ($totalResults < 0){
            $totalResults = 0;
        }
        $this->_totalResults = $totalResults;
    }
    
    /**
     * Getter for the $_totalResults property
     * @return int
     */
    public function getTotalResults(): int {
        return $this->_totalResults;
    }
    
    /**
     * Setter for the $_totalPages property
     * @param int $totalPages
     */
    public function setTotalPages(int $totalPages) {
        if ($totalPages < 1){
            $totalPages = 1;
        }
        $this->_totalPages = $totalPages;
        $this->_texts = $this->processTexts();
    }
    
    /**
     * Getter for the $_totalPages property
     * @return int
     */
    public function getTotalPages(): int{
        return $this->_totalPages;
    }
    
    /**
     * Setter for the $_basePath property
     * @param string $basePath
     */
    public function setBasePath(string $basePath = ""){
        $this->_basePath = $basePath;
    }
    
    /**
     * Method used to process the buttons that will be rendered
     * @return array
     */
    public function processTexts(): array {
        
        $mid = floor($this->maxPagesDisplayed / 2);
        
        $firstPageToDisplay = max(1, abs($this->_page - $mid));
        $lastPageToDisplay  = min($this->_totalPages, $this->_page + $mid);

        // incercam sa afisam numarul maximum de pagini
        while ($this->maxPagesDisplayed-1 > ($lastPageToDisplay - $firstPageToDisplay) && $firstPageToDisplay > 1){
            $firstPageToDisplay--;
        }
        while ($this->maxPagesDisplayed-1 > ($lastPageToDisplay - $firstPageToDisplay) && $lastPageToDisplay < $this->_totalPages){
            $lastPageToDisplay++;
        }
        
        $pages = range($firstPageToDisplay, $lastPageToDisplay);
        
        $texts = array();
        foreach ($pages as $i => $page) {
            $texts[$page] = array(
                "page" => $page,
                "text" => $page,
                "title" => $page,
                "ariaLabel" => $page
            );
        }
        
        $beginPoints = false;
        $endPoints = false;
        
        if ($this->showFirstAndLast && !isset($texts[1])) {
            $texts[-1] = array(
                "page" => 1,
                "text" => 1,
                "title" => Cux::translate("core.paginator", "First page", array(), "Title for the 'First page' button for paginators")
            );
            if ($firstPageToDisplay > 1){
                $beginPoints = true;
                $texts[0] = array(
                    "page" => 0,
                    "text" => "..",
                    "title" => "",
                    "disabled" => true
                );
            }
        }
        if ($this->showFirstAndLast && !isset($texts[$this->_totalPages])) {
            if ($lastPageToDisplay < $this->_totalPages){
                $endPoints = true;
                $texts[$this->_totalPages] = array(
                    "page" => 0,
                    "text" => "..",
                    "title" => "",
                    "disabled" => true
                );
            }
            $texts[$this->_totalPages + 2] = array(
                "page" => $this->_totalPages,
                "text" => $this->_totalPages,
                "title" => Cux::translate("core.paginator", "Last page", array(), "Title for the 'Last page' button for paginators")
            );
        }
        
        ksort($texts);
        
        if (count($texts) > $this->maxPagesDisplayed){
            if ($beginPoints){
                $texts[$firstPageToDisplay] = null;
                unset($texts[$firstPageToDisplay]);
                
            }
            if ($endPoints){
                $texts[$lastPageToDisplay] = null;
                unset($texts[$lastPageToDisplay]);
            }
            
            if (count($texts) > $this->maxPagesDisplayed){
                if ($beginPoints){
                    $texts[$firstPageToDisplay+1] = null;
                    unset($texts[$firstPageToDisplay+1]);
                }
                if ($endPoints){
                    $texts[$lastPageToDisplay - 1] = null;
                    unset($texts[$lastPageToDisplay - 1]);
                }
            }
            
        }
        
        return $texts;
    }

}
