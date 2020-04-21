<?php

namespace CuxFramework\utils;

use CuxFramework\utils\CuxObject;
use CuxFramework\components\db\CuxDBCriteria;
use CuxFramework\components\html\CuxHTML;

class CuxSorter{
    
    private $_model;
    private $_crit;
    private $_sortParam = "sort";
    
    private $_sortField;
    private $_sortOrder;
    
    private $_defaultSortField;
    private $_defaultSortOrder;
    
    private $_sortFields = array();
    
    public function __construct(CuxObject $model) {
        $this->_model = $model;
        $this->setSortFields();
    }
    
    public function setSortFields(array $sortFields = null){        
        if (is_null($sortFields)){ // set the fields priovided by the model
            $attributes = $this->_model->getAttributes();
            foreach ($attributes as $attribute => $foo){
                $this->_sortFields[$attribute] = $attribute;
            }
        } else {
            $this->_sortFields = array(); // unset previous model sort fields
            foreach ($sortFields as $alias => $field){
                if ($this->_model->hasAttribute($field)){
                    $this->_sortFields[$alias] = $field;
                }
            }
        }
    }
    
    private function getCrtSortDetails(){
        $crtLink = Cux::getInstance()->request->getUri();
        $sort = Cux::getInstance()->request->getParam($this->_sortParam, "");
        if ($sort){            
            $sortInfo = explode(".", $sort);
            $sortField = $sortInfo[0];
            $sortOrder = strtolower($sortInfo[1]);
            if (isset($this->_sortFields[$sortField])){
//                $this->_sortField = $sortField;
//                $this->_sortOrder = $sortOrder;
                return [$sortField, $sortOrder];
            }
        } elseif ($this->_defaultSortField){
            return [$this->_defaultSortField, $this->_defaultSortOrder ? $this->_defaultSortOrder : "asc"];
        } else {
            return ["", "asc"];
        }
    }
    
    public function setDefaultSortOrder(string $sortField, string $sortOrder){
        if (isset($this->_sortFields[$sortField])){
            $this->_defaultSortField = $sortField;
            $this->_defaultSortOrder = strtolower($sortOrder);            
            return true;
        }
        return false;
    }
    
    public function setSortParam(string $sortParam){
        $this->_sortparam = $sortParam;
    }
    
    public function applyCriteria(CuxDBCriteria &$criteria){
        $this->_crit = $criteria;
        
        list($crtSortField, $crtSortOrder) = $this->getCrtSortDetails();
        
        if ($crtSortField && isset($this->_sortFields[$crtSortField])){
            $this->_crit->order = $this->_sortFields[$crtSortField]." ".strtoupper($crtSortOrder);
        }
    }
    
    public function sortLink(string $field, string $content){
        
        if (!isset($this->_sortFields[$field])){
            return $content;
        }
        
        list($crtSortField, $crtSortOrder) = $this->getCrtSortDetails();
        
        $sortField = $field;
        $sortOrder = "asc";
        
        $crtLink = Cux::getInstance()->request->getUri();
        
        if ($crtSortField){
            
            $icon = "";
            if ($crtSortField == $sortField){
                $sortOrder =  ($crtSortOrder == "asc") ? "desc" : "asc";
                $icon = "<span class='fas fa-caret-".($sortOrder == "asc" ? "down" : "up")."'></span>";
            }
            
            $sort = Cux::getInstance()->request->getParam($this->_sortParam, "");
            if ($sort){
              return CuxHTML::a($content."&nbsp;".$icon, str_replace($sort, $sortField.".".$sortOrder, $crtLink));  
            } elseif (strpos($crtLink, "?") !== false){
                return CuxHTML::a($content."&nbsp;".$icon, $crtLink."&".$this->_sortParam."=".$sortField.".".$sortOrder);
            } else {
                return CuxHTML::a($content."&nbsp;".$icon, $crtLink."?".$this->_sortParam."=".$sortField.".".$sortOrder);
            }
        } else {
            if (strpos($crtLink, "?") !== false){
                return CuxHTML::a($content, $crtLink."&".$this->_sortParam."=".$sortField.".".$sortOrder);
            } else {
                return CuxHTML::a($content, $crtLink."?".$this->_sortParam."=".$sortField.".".$sortOrder);
            }
        }
        
    }
    
}

