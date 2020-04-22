<?php

namespace CuxFramework\utils;

use CuxFramework\utils\Cux;

abstract class CuxBasePaginator {

    public $showFirstAndLast = true;
    
    public $maxPagesDisplayed = 10;
    
    protected $_page = 1;
    protected $_totalPages = 1;
    protected $_basePath;
    protected $_pageParam = "page";
    protected $_texts = array();

    public abstract function render(): string;
    
    public function __construct(int $page, int $totalPages, string $basePath = "") {
        
        $this->setPage($page);
        $this->setTotalPages($totalPages);
        $this->setBasePath($basePath);
        
        if ($this->maxPagesDisplayed < 7){
            $this->maxPagesDisplayed = 7;
        }
        
        $this->_texts = $this->processTexts();
        
    }
    
    public function setPageParam(string $pageParam){
        $this->_pageParam = $pageParam;
    }
    
    public function getPageParam(): string{
        return $this->_pageParam;
    }
    
    public function setPage(int $page) {
        if ($page < 1){
            $page = 1;
        }
        $this->_page = $page;
        
        $this->_texts = $this->processTexts();
    }
    
    public function getPage(): int {
        return $this->_page;
    }
    
    public function setTotalPages(int $totalPages) {
        if ($totalPages < 1){
            $totalPages = 1;
        }
        $this->_totalPages = $totalPages;
        $this->_texts = $this->processTexts();
    }
    
    public function getTotalPages(): int{
        return $this->_totalPages;
    }
    
    public function setBasePath(string $basePath = ""){
        $this->_basePath = $basePath;
    }
    
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
