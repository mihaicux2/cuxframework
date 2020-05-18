<?php

namespace CuxFramework\utils;

use CuxFramework\utils\CuxObject;
use CuxFramework\components\db\CuxDBCriteria;
use CuxFramework\components\html\CuxHTML;

class CuxSorter {

    private $_model;
    private $_crit;
    private $_sortParam = "sort";
    private $_sortField;
    private $_sortOrder;
    private $_defaultSortField;
    private $_defaultSortOrder;
    private $_sortFields = array();
    private $_multiSort = false;

    public function __construct(CuxObject $model) {
        $this->_model = $model;
        $this->setSortFields();
    }

    public function setMultiSort(bool $multiSort) {
        $this->_multiSort = $multiSort;
    }

    public function setSortFields(array $sortFields = null) {
        if (is_null($sortFields)) { // set the fields priovided by the model
            $attributes = $this->_model->getAttributes();
            foreach ($attributes as $attribute => $foo) {
                $this->_sortFields[$attribute] = $attribute;
            }
        } else {
            $this->_sortFields = array(); // unset previous model sort fields
            foreach ($sortFields as $alias => $field) {
//                if ($this->_model->hasAttribute($field)) {
                    $this->_sortFields[$alias] = $field;
//                }
            }
        }
    }

    private function getCrtSortDetails() {
        $params = Cux::getInstance()->request->getParams();
        $sort = isset($params[$this->_sortParam]) ? $params[$this->_sortParam] : array();

        $ret = array();

        if (!empty($sort)) {
            foreach ($sort as $it => $sortPair) {
                $sortInfo = explode(".", $sortPair);
                $sortField = $sortInfo[0];
                $sortOrder = strtolower($sortInfo[1]);
                if (isset($this->_sortFields[$sortField])) {
                    $ret[] = array(
                        "sortField" => $sortField,
                        "sortOrder" => $sortOrder
                    );
                }
            }
        }

        if (empty($ret)) {
            if ($this->_defaultSortField) {
                $ret[] = array(
                    "sortField" => $this->_defaultSortField,
                    "sortOrder" => $this->_defaultSortOrder ? $this->_defaultSortOrder : "asc"
                );
            }
        }
        
        if (!$this->_multiSort && count($ret) > 1){
            $ret = array($ret[0]);
        }

        return $ret;
    }

    public function setDefaultSortOrder(string $sortField, string $sortOrder) {
        if (isset($this->_sortFields[$sortField])) {
            $this->_defaultSortField = $sortField;
            $this->_defaultSortOrder = strtolower($sortOrder);
            return true;
        }
        return false;
    }

    public function setSortParam(string $sortParam) {
        $this->_sortparam = $sortParam;
    }

    public function applyCriteria(CuxDBCriteria &$criteria) {
        $this->_crit = $criteria;

        $sortCrit = [];
        $sortDetails = $this->getCrtSortDetails();

        if (!empty($sortDetails)) {
            foreach ($sortDetails as $sortData) {
                if (isset($this->_sortFields[$sortData["sortField"]])) {
                    $sortCrit[] = $this->_sortFields[$sortData["sortField"]] . " " . strtoupper($sortData["sortOrder"]);
                }
            }
        }

        if (!empty($sortCrit)) {
            $this->_crit->order = implode(", ", $sortCrit);
        }
    }

    public function sortLink(string $field, string $content) {

        if (!isset($this->_sortFields[$field])) {
            return $content;
        }

        $sortDetails = $this->getCrtSortDetails();

        $sortField = $field;
        $sortOrder = "asc";

//        $crtLink = Cux::getInstance()->request->getUri();

        $params = Cux::getInstance()->request->getParams();
//        if (isset($params[$this->_sortParam])) {
//            $params[$this->_sortParam] = null;
//            unset($params[$this->_sortParam]);
//        }

        $crtLink = Cux::getInstance()->request->getRoutePath();
        $path = $crtLink;
        if (!empty($params)){
            $paramsArr = array();
            foreach ($params as $key => $val){
//                if ($key == $sortField){
//                    $sortOrder = ($sortData["sortOrder"] == "asc") ? "desc" : "asc";
//                }
                if ($key == $this->_sortParam) continue;
                if (!is_array($val)){
                    $path .= "/{$key}/{$val}";
                } else {
                    foreach ($val as $val2){
                        $path .= "/{$key}[]/{$val2}";
                    }
                }
            }
        }

        if (!empty($sortDetails)) {
            $sortArr = array();
            $icon = "";
            $fieldFound = false;
            $it = 0;
            foreach ($sortDetails as $sortData) {
                if ($sortData["sortField"] == $sortField) {
                    $sortOrder = ($sortData["sortOrder"] == "asc") ? "desc" : "asc";
                    $fieldFound = true;
                } else {
                    $sortArr[$it] = $this->_sortParam . (($this->_multiSort) ? "[]" : "") . "/" . $sortData["sortField"] . "." . $sortData["sortOrder"];
                }
                $it++;
            }

            $title = $sortOrder == "asc" ? Cux::translate("core.sorter", "Sort ascending") : Cux::translate("core.sorter", "Sort descending");
            
            if ($fieldFound){
                $icon = "<span class='fas fa-sort-amount-" . ($sortOrder == "asc" ? "down" : "up") . " text-info'></span>";
            }
            
            if (!$this->_multiSort) {
                $sortArr = array($this->_sortParam . (($this->_multiSort) ? "[]" : "") . "/" . $sortField . "." . $sortOrder);
            } else {
                array_unshift($sortArr, $this->_sortParam . (($this->_multiSort) ? "[]" : "") . "/" . $sortField . "." . $sortOrder);
            }

            $retLink = CuxHTML::a($content . "&nbsp;" . $icon, $path . "/" . implode("/", $sortArr), array("title" => $title));
            if ($this->_multiSort == true && count($sortArr) > 1 && $fieldFound) {
                array_splice($sortArr, 0, 1);
                $retLink .= "&nbsp;" . CuxHTML::a("<span class='fas fa-times text-danger' title='" . Cux::translate("core.sorter", "Remove sorting criiteria") . "'></span>", $path . "/" . implode("/", $sortArr));
            }

            return $retLink;
        } else {
            $sortField = $this->_sortParam . (($this->_multiSort) ? "[]" : "") . "/" . $sortField . "." . $sortOrder;
            return CuxHTML::a($content, $crtLink . "/" . $this->_sortParam . "/" . $sortField . "." . $sortOrder, array("title" => $title));
        }
    }

}
