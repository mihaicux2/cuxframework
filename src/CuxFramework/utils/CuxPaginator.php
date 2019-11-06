<?php

namespace CuxFramework\utils;

class CuxPaginator {

    public $showFirstAndLast = true;
    public $maxPagesDisplayed = 10;
    public $pageParam = "page";
    
    private $_page = 1;
    private $_totalPages = 1;
    private $_basePath;
    private $_texts = array();

    public function __construct(int $page, int $totalPages, string $basePath = "") {

        $this->_page = $page;
        $this->_totalPages = $totalPages;
        $this->_basePath = $basePath;
        
        if ($this->maxPagesDisplayed < 7){
            $this->maxPagesDisplayed = 7;
        }
        if ($this->_page < 1){
            $this->_page = 1;
        }
        if ($this->_totalPages < 1){
            $this->_totalPages = 1;
        }
        
        $this->_texts = $this->processTexts();
        
    }

    public function render(): string{
        
        if ($this->_totalPages < 2)
            return "";
        
        if (!$this->_basePath || empty($this->_basePath)){
            $this->_basePath = Cux::getInstance()->request->getPath();
        }
        
        if (($pos = strpos($this->_basePath, "?")) !== false){
            $this->_basePath = substr($this->_basePath, 0, $pos);
        }
        
        $path = $this->_basePath;
        if (strpos($path, $this->pageParam."/".$this->_page) == false){
            $path .= "/".$this->pageParam."/".$this->_page;
        }
        
        $params = Cux::getInstance()->request->getParams();
        if (!empty($params)){
            $path .= "?".http_build_query($params);
        }
        
        $pages = array();
        foreach ($this->_texts as $i => $text){
            $class = ($this->_page == $text["page"]) ? " class='active'" : "";
            if (!isset($text["disabled"])){
                $link = str_replace($this->pageParam."/".$this->_page, $this->pageParam."/".$text["page"], $path);
                $pages[] = "<li$class><a href='$link' title='".$text["title"]."'>".$text["text"]."</a></li>";
            }
            else{
                $pages[] = "<li class='disabled'><a title='".$text["title"]."'>".$text["text"]."</a></li>";
            }
        }
        
        $str  = "<nav aria-label=\"Page navigation\">";
        $str .= "<ul class=\"pagination\">";
        $str .= implode("", $pages);
        $str .= "</ul>";
        $str .= "</nav>";
        
        return $str;
        
    }
    
    private function processTexts(): array {
        
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
                "title" => "Prima"
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
                "title" => "Ultima"
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
