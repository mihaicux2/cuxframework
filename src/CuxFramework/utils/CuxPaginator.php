<?php

/**
 * CuxPaginator class file
 * 
 * @package Utils
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\utils;

/**
 * Class that can be used to render a pagination system / paginator
 */
class CuxPaginator extends CuxBasePaginator{

    /**
     * Class constructor
     * @param int $page - the current page
     * @param int $totalPages - total pages
     * @param string $basePath - the base path for ( to be- ) generated links
     */
    public function __construct(int $page, int $totalPages, string $basePath = "") {

        parent::__construct($page, $totalPages, $basePath);
        
    }

    /**
     * Actual rendering of the generated page links/buttons
     * @return string
     */
    public function render(): string{
        
        if ($this->_totalPages < 2)
            return "";
        
        if (!$this->_basePath || empty($this->_basePath)){
            $this->_basePath = Cux::getInstance()->request->getRoutePath();
        }
        
        if (($pos = strpos($this->_basePath, "?")) !== false){
            $this->_basePath = substr($this->_basePath, 0, $pos);
        }
        
        $path = $this->_basePath;        
        
//        $route = Cux::getInstance()->urlManager->getMatchedRoute();
//        $routeInfo = $route->getDetails();
//        print_r($routeInfo);
        
        
        
        $params = Cux::getInstance()->request->getParams();
        if (!empty($params)){
            $paramsArr = array();
//            $path .= "?".http_build_query($params);
            foreach ($params as $key => $val){
                if (!is_array($val)){
                    $path .= "/{$key}/{$val}";
                } else {
                    foreach ($val as $val2){
                        $path .= "/{$key}[]/{$val2}";
                    }
                }
            }
        }
        
         if (strpos($path, $this->_pageParam."/".$this->_page) === false){
            $path .= "/".$this->_pageParam."/".$this->_page;
        }
        
        $pages = array();
        foreach ($this->_texts as $i => $text){
            $class = ($this->_page == $text["page"]) ? " active" : "";
            if (!isset($text["disabled"])){
                $link = str_replace($this->_pageParam."/".$this->_page, $this->_pageParam."/".$text["page"], $path);
                $pages[] = "<li class='page-item{$class}'><a class='page-link' href='$link' title='".$text["title"]."'>".$text["text"]."</a></li>";
            }
            else{
                $pages[] = "<li class='page-item disabled'><a class='page-link' title='".$text["title"]."'>".$text["text"]."</a></li>";
            }
        }
        
        $str  = "<nav aria-label=\"Page navigation\">";
        $str .= "<ul class=\"pagination\">";
        $str .= implode("", $pages);
        $str .= "</ul>";
        $str .= "</nav>";
        
        return $str;
        
    }

}
