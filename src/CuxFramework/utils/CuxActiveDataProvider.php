<?php

namespace CuxFramework\utils;

use CuxFramework\utils\Cux;
use CuxFramework\components\db\CuxDBObject;
use CuxFramework\components\db\CuxDBCriteria;
use CuxFramework\components\html\CuxHTML;

class CuxActiveDataProvider extends CuxDataProvider {

    private $_model;
    private $_pager;
    private $_sorter;
    private $_criteria;
    private $_data;
    private $_filter = array();
    private $_pageSize;
    private $_template = "<div>{filter}</div>
         <div>{pager}</div>
         <div>{summary}</div>
         <div>{header}</div>
         <div>{list}</div>
         <div>{footer}</div>
         <div>{summary}</div>
         <div>{pager}</div>";

    public function __construct(CuxDbObject $model, array $options = array()) {
        $this->_model = $model;
        parent::__construct($options);

        $isMobile = $this->getIsMobile();

        if (!isset($options["columns"]) && !$isMobile) {
            $columns = array();
            foreach ($this->_model->getAttributes() as $key => $val) {
                $columns[$key] = array(
                    "key" => $key,
                    "value" => $key,
                    "label" => $this->_model->getLabel($key)
                );
            }
            $this->setColumns($columns);
        }

        if (isset($options["criteria"]) && $options["criteria"] instanceof CuxDBCriteria) {
            $this->setCriteria($options["criteria"]);
        } else {
            $this->setCriteria(new CuxDBCriteria());
        }

        $this->setupFilter();

        $this->setPageSize(static::ROWS_PER_PAGE);

        if (isset($options["pageSize"]) && (int) $options["pageSize"]) {
            $this->setPageSize((int) $options["pageSize"]);
        }

        if (isset($options["pager"]) && $options["pager"] instanceof CuxBasePaginator) {
            $this->setPager($options["pager"]);
        } else {
            $pager = new CuxPaginator(1, 1);
            $this->setPager($pager);
        }

        if (isset($options["template"]) && !empty($options["template"])) {
            $this->setTemplate($options["template"]);
        }

        if (isset($options["sorter"]) && $options["sorter"] instanceof CuxSorter) {
            $this->setSorter($options["sorter"]);
        } else {
            $sorter = new CuxSorter($model);
            $sorter->setMultiSort(true);
            $pks = $this->_model->getPk();
            $keys = array_keys($pks);
            $sorter->setDefaultSortOrder($keys[0], "asc");
            $this->setSorter($sorter);
        }

        $params = Cux::getInstance()->getActionParams();
        $pageParam = $this->_pager->getPageParam();
        $page = (int) (isset($params[$pageParam]) ? $params[$pageParam] : Cux::getInstance()->request->getParam($pageParam, 1));
        if (!$page) {
            $page = 1;
        }

        $total = $this->_model->countAllByCondition($this->_criteria);
        $pageSize = $this->getPageSize();
        $totalPages = ceil(($total > 0) ? $total / $pageSize : 1);

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $this->_pager->setTotalResults($total);
        $this->_pager->setTotalPages($totalPages);
        $this->_pager->setPage($page);

        $this->_criteria->limit = $pageSize;
        $this->_criteria->offset = $pageSize * ($page - 1);

        $this->_sorter->applyCriteria($this->_criteria);

        $this->_data = $this->_model->findAllByCondition($this->_criteria);
    }

    public function setTemplate(string $template = "") {
        $this->_template = $template;
    }

    public function getTemplate(): string {
        return $this->_template;
    }

    private function setupFilter() {
        if ($this->getShowFilter()) {
            $columns = $this->getColumns();
            foreach ($columns as $key => $columnDetails) {
                $column = isset($columnDetails["column"]) ? $columnDetails["column"] : false;
                if (!$column)
                    continue;
                if (isset($columnDetails["filter"])) {
                    switch ($columnDetails["filter"]) {
                        case "off":
                            break;
                        case "search":
                            break;
                        case "interval":
                            $minVal = (int) Cux::getInstance()->request->getParam($columnDetails["key"] . "_min");
                            $maxVal = (int) Cux::getInstance()->request->getParam($columnDetails["key"] . "_max");

                            if ($minVal > 0 && $maxVal > 0) {
                                $this->_criteria->addCondition("{$column} >= :{$key}_minVal AND {$column} <= :{$key}_maxVal");
                                $this->_criteria->params[":{$key}_minVal"] = $minVal;
                                $this->_criteria->params[":{$key}_maxVal"] = $maxVal;

                                $this->_filter[] = array(
                                    "field" => $this->_model->getAttributeLabel($column),
                                    "operator" => ":",
                                    "value" => "[{$minVal} " . Cux::translate("core.dataProvider", "and", array(), "AND conjunction") . " {$maxVal}]"
                                );
                            } elseif ($minVal > 0) {
                                $this->_criteria->addCondition("{$column} >= :{$key}_minVal");
                                $this->_criteria->params[":{$key}_minVal"] = $minVal;

                                $this->_filter[] = array(
                                    "field" => $this->_model->getAttributeLabel($column),
                                    "operator" => ">=",
                                    "value" => $minVal
                                );
                            } elseif ($maxVal > 0) {
                                $this->_criteria->addCondition("{$column} <= :{$key}_maxVal");
                                $this->_criteria->params[":{$key}_maxVal"] = $maxVal;

                                $this->_filter[] = array(
                                    "field" => $this->_model->getAttributeLabel($column),
                                    "operator" => "<=",
                                    "value" => $maxVal
                                );
                            }

                            break;
                        case "present":
                            $val = (int) Cux::getInstance()->request->getParam($columnDetails["key"]);
                            if ($val > 0) { // 0 - N/A, 1 - Yes, 2 - NO
                                if ($val == 1) {
                                    $this->_criteria->addCondition("{$column}=1");
                                    $this->_filter[] = array(
                                        "field" => $this->_model->getAttributeLabel($column),
                                        "operator" => ":",
                                        "value" => Cux::translate("core.dataProvider", "Yes", array(), "YES, AFFIRMATIVE, OK")
                                    );
                                } else {
                                    $this->_criteria->addCondition("{$column}=0");

                                    $this->_filter[] = array(
                                        "field" => $this->_model->getAttributeLabel($column),
                                        "operator" => ":",
                                        "value" => Cux::translate("core.dataProvider", "No", array(), "NO, NEGATIVE, NOT OK")
                                    );
                                }
                            }
                            break;
                        case "text":
                        case "list":
                        default:
                            $val = trim(Cux::getInstance()->request->getParam($columnDetails["key"]));
                            if ($val) {
                                $this->_criteria->addCondition("{$column} LIKE :{$key}_val");
                                $this->_criteria->params[":{$key}_val"] = "%{$val}%";

                                $this->_filter[] = array(
                                    "field" => $this->_model->getAttributeLabel($column),
                                    "operator" => ":",
                                    "value" => $val
                                );
                            }
                    }
                } else {
                    $val = trim(Cux::getInstance()->request->getParam($columnDetails["key"]));
                    if ($val) {
                        $this->_criteria->addCondition("{$column} LIKE :{$key}_val");
                        $this->_criteria->params[":{$key}_val"] = "%{$val}%";

                        $this->_filter[] = array(
                            "field" => $this->_model->getAttributeLabel($column),
                            "operator" => ":",
                            "value" => $val
                        );
                    }
                }
            }
        }
    }

    public function getFilter(): array {
        if (!empty($this->_filter)) {
            $arr = array();
            foreach ($this->_filter as $filter) {
                $arr[] = "<b>" . $filter["field"] . "</b> " . $filter["operator"] . " <b>" . $filter["value"] . "</b>";
            }
            return $arr;
        }
        return array();
    }

    public function setPageSize(int $pageSize) {
        $this->_pageSize = $pageSize;
    }

    public function getPageSize(): int {
        return $this->_pageSize;
    }

    public function setCriteria(CuxDBCriteria $criteria) {
        $this->_criteria = $criteria;
    }

    public function getCriteria(): CuxDBCriteria {
        return $this->_criteria;
    }

    public function setPager(CuxBasePaginator $pager) {
        $this->_pager = $pager;
    }

    public function getPager(): CuxBasePaginator {
        return $this->_pager;
    }

    public function setSorter(CuxSorter $sorter) {
        $this->_sorter = $sorter;
    }

    public function getSorter(): CuxSorter {
        return $this->_sorter;
    }

    private function getLabels(): array {
        return $this->_model->labels();
    }

    private function randomString(int $length = 10): string {
        $ret = "";
        $alphabet = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $max = mb_strlen($alphabet, '8bit') - 1;
        for ($i = 0; $i < $length; $i++) {
            $ret .= $alphabet[rand(0, $max)];
        }
        return $ret;
    }

    private function renderPager(): string {
        return $this->_pager->render();
    }

    private function renderFilter(): string {
        $str = "";
        $filter = $this->getFilter();
        if (!empty($filter)) {
            $str = implode("<br />", $filter);
        }
        return $str;
    }

    private function renderSummary(): string {
        return Cux::translate("core.dataProvider", "Page", array(), "Page number, current page") . ": " . $this->_pager->getPage() . " / " . $this->_pager->getTotalPages() . ". " . Cux::translate("core.dataProvider", "Total results", array(), "") . ": " . $this->_pager->getTotalResults();
        ;
    }

    private function renderHeader(): string {
        return $this->getHeader();
    }

    private function renderFooter(): string {
        return $this->getFooter();
    }

    private function renderSearchFilter($id, $columns): string {
        $str = "<tr id=\"form_{$id}\">";
        foreach ($columns as $key => $columnDetails) {
            $str .= "<th>";
            $str .= $this->renderColumnFilter($columnDetails, $id);
            $str .= "</th>";
        }
        $str .= "</tr>";
        return $str;
    }

    private function renderJS($id, $columns): string {
        $params = Cux::getInstance()->request->getParams();
        $crtLink = Cux::getInstance()->request->getRoutePath();

        $str = "<script>";

        // clearSearch
        $str .= "function clearSearch_{$id}(){
            var baseUrl = \"{$crtLink}\";
            var params = {};";
        if (!empty($params)) {
            foreach ($params as $key => $val) {
                if (!is_array($val)) {
                    $str .= "params[\"{$key}\"] = \"{$val}\";";
                } else {
                    $str .= "params[\"{$key}\"] = new Array();";
                    foreach ($val as $val2) {
                        $str .= "params[\"{$key}\"].push(\"{$val2}\");";
                    }
                }
            }
        }

        foreach ($columns as $key => $columnDetails) {
            if (isset($columnDetails["filter"])) {
                switch ($columnDetails["filter"]) {
                    case "off":
                    case "search":
                        break;
                    case "interval":
                        $str .= "params[\"" . $columnDetails["key"] . "_min\"] = \"\";";
                        $str .= "params[\"" . $columnDetails["key"] . "_max\"] = \"\";";
                        break;
                    case "present":
                    case "text":
                    case "list":
                    default:
                        $str .= "params[\"" . $columnDetails["key"] . "\"] = \"\";";
                }
            } else {
                $str .= "params[\"" . $columnDetails["key"] . "\"] = \"\";";
            }
        }

//        $str .= "console.log(params); return;";

        $str .= 'var url = baseUrl;
                var keys = Object.keys(params);
                for (var i = 0; i < keys.length; i++){
                    var value = params[keys[i]];
                    if (value.constructor == Array){
                        for (var j = 0; j < value.length; j++){
                            if (value[j] != ""){
                                url += "/"+keys[i]+"[]"+"/"+value[j];
                            }
                        }
                    } else {
                        if (value != ""){
                            url += "/"+keys[i] + "/" + value;
                        }
                    }
                }';
        $str .= "location.href = url;";

        $str .= "}"; // end of clearSearch
        // doSearch
        $str .= "function doSearch_{$id}(){
            var baseUrl = \"{$crtLink}\";
            var params = {};\n";

        if (!empty($params)) {
            foreach ($params as $key => $val) {
                if (!is_array($val)) {
                    $str .= "params[\"{$key}\"] = \"{$val}\";\n";
                } else {
                    $str .= "params[\"{$key}\"] = new Array();\n";
                    foreach ($val as $val2) {
                        $str .= "params[\"{$key}\"].push(\"{$val2}\");\n";
                    }
                }
            }
        }

        foreach ($columns as $key => $columnDetails) {
            if (isset($columnDetails["filter"])) {
                switch ($columnDetails["filter"]) {
                    case "off":
                    case "search":
                        break;
                    case "interval":
                        $str .= "params[\"" . $columnDetails["key"] . "_min\"] = $(\"#{$id}_" . $columnDetails["key"] . "_min\").val();\n";
                        $str .= "params[\"" . $columnDetails["key"] . "_max\"] = $(\"#{$id}_" . $columnDetails["key"] . "_max\").val();\n";
                        break;
                    case "present":
                    case "text":
                    case "list":
                    default:
                        $str .= "params[\"" . $columnDetails["key"] . "\"] = $(\"#{$id}_" . $columnDetails["key"] . "\").val();\n";
                }
            } else {
                $str .= "params[\"" . $columnDetails["key"] . "\"] = $(\"#{$id}_" . $columnDetails["key"] . "\").val();\n";
            }
        }

        $str .= 'var url = baseUrl;
                var keys = Object.keys(params);
                for (var i = 0; i < keys.length; i++){
                    var value = params[keys[i]];
                    if (value.constructor == Array){
                        for (var j = 0; j < value.length; j++){
                            if (value[j] != ""){
                                url += "/"+keys[i]+"[]"+"/"+value[j];
                            }
                        }
                    } else {
                        if (value != ""){
                            url += "/"+keys[i] + "/" + value;
                        }
                    }
                }';
        $str .= "location.href = url;";

        $str .= "}"; // end of doSearch

        $str .= "</script>";

        return $str;
    }

    private function renderTableHead($columns): string {
        $str = "<tr>";
        foreach ($columns as $key => $columnDetails) {
            $width = isset($columnDetails["width"]) ? " width='".$columnDetails["width"]."'" : "";
            $str .= "<th{$width}>" . $this->_sorter->sortLink($key, $columnDetails["label"]) . "</th>";
        }
        $str .= "</tr>";

        return $str;
    }

    private function renderColumnFilter($columnDetails, $id): string{
        $str = "";
        if (isset($columnDetails["filter"])) {
            switch ($columnDetails["filter"]) {
                case "off":
                    break;
                case "search":
                    if (!$this->getIsMobile()){
//                        $str .= CuxHTML::button(CuxHTML::tag("span", "", array("class" => "fas fa-search")), array("class" => "btn btn-sm btn-success2", "onclick" => "doSearch_{$id}()"));
//                        $str .= "&nbsp;";
//                        $str .= CuxHTML::button(CuxHTML::tag("span", "", array("class" => "fas fa-redo")), array("class" => "btn btn-sm btn-info2", "onclick" => "clearSearch_{$id}()"));
                        $str .= CuxHTML::button(CuxHTML::tag("span", "", array("class" => "fas fa-search"))."&nbsp;".Cux::translate("core.dataProvider", "Search", array(), "Mobile version of the data provider"), array("class" => "btn btn-sm btn-success", "onclick" => "doSearch_{$id}()"));
                        $str .= "&nbsp;";
                        $str .= CuxHTML::button(CuxHTML::tag("span", "", array("class" => "fas fa-redo"))."&nbsp;".Cux::translate("core.dataProvider", "Reset", array(), "Mobile version of the data provider"), array("class" => "btn btn-sm btn-info", "onclick" => "clearSearch_{$id}()"));
                    }
                    break;
                case "interval":
                    $str .= CuxHTML::textInput($columnDetails["key"] . "_min", Cux::getInstance()->request->getParam($columnDetails["key"] . "_min"), array("style" => "width:45%;", "class" => "form-control float-left", "id" => $id . "_" . $columnDetails["key"] . "_min"));
                    $str .= "&nbsp;";
                    $str .= CuxHTML::textInput($columnDetails["key"] . "_max", Cux::getInstance()->request->getParam($columnDetails["key"] . "_max"), array("style" => "width:45%;;", "class" => "form-control float-left", "id" => $id . "_" . $columnDetails["key"] . "_max"));
                    break;
                case "list":
                    $str .= CuxHTML::dropdownList($columnDetails["key"], Cux::getInstance()->request->getParam($columnDetails["key"]), $columnDetails["options"], array(
                                "class" => "form-control",
                                "id" => $id . "_" . $columnDetails["key"]
                    ));
                    break;
                case "present":
                    $str .= CuxHTML::dropdownList($columnDetails["key"], Cux::getInstance()->request->getParam($columnDetails["key"]), array(
                                "" => Cux::translate("core.dataProvider", "Choose", array(), "Select some value"),
                                "1" => Cux::translate("core.dataProvider", "Yes", array(), "YES, AFFIRMATIVE, OK"),
                                "2" => Cux::translate("core.dataProvider", "No", array(), "NO, NEGATIVE, NOT OK")
                                    ), array(
                                "class" => "form-control",
                                "id" => $id . "_" . $columnDetails["key"]
                    ));
                    break;
                case "text":
                default:
                    $str .= CuxHTML::textInput($columnDetails["key"], Cux::getInstance()->request->getParam($columnDetails["key"]), array(
                                "class" => "form-control",
                                "id" => $id . "_" . $columnDetails["key"]
                    ));
            }
        } else {
            $str .= CuxHTML::textInput($columnDetails["key"], Cux::getInstance()->request->getParam($columnDetails["key"]), array(
                        "class" => "form-control",
                        "id" => $id . "_" . $columnDetails["key"]
            ));
        }
        return $str;
    }
    
    private function renderMobileHead($columns, $id): string {
        $showFilter = $this->getShowFilter();
        $str = "";
        foreach ($columns as $key => $columnDetails) {
            $str .= "<div>";
            $str .= "<div>".$this->_sorter->sortLink($key, $columnDetails["label"])."</div>";
            if ($showFilter) {
                $str .= "<div>".$this->renderColumnFilter($columnDetails, $id)."</div>";
            }
            $str .= "<div class='clearfix'></div>";
            $str .= "</div>";
        }

        return $str;
    }

    private function renderList(): string {

        $columns = $this->getColumns();
        $id = $this->randomString(5);

        $isMobile = $this->getIsMobile();

        $str = "<table class='" . $this->getTableClass() . "'>";
        $str .= "<thead class='" . $this->getTHeadClass() . "'>";
        if (!$isMobile) {
            $str .= $this->renderTableHead($columns);
            if ($this->getShowFilter()) {
                $str .= $this->renderSearchFilter($id, $columns);
            }
        } else {
            $str .= "<tr id=\"form_{$id}\"><th>";
            $str .= CuxHTML::a('<span class="fas fa-filter"></span>&nbsp;|&nbsp;<span class="fas fa-search"></span>&nbsp;'.Cux::translate("core.dataProvider", "Sort by / filter", array(), "Mobile version of the data provider"), "javascript:void(0)", array("onclick"=> "$(\"#listFilters_{$id}\").modal('show')"));
            $str .= "<div id='listFilters_{$id}' class='modal fade'  role='dialog' aria-labelledby='listFiltersBoxTitle_{$id}' aria-hidden='true'>";
            $str .= '<div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="listFiltersBoxTitle_'.$id.'"><span class="fas fa-filter"></span>&nbsp;|&nbsp;<span class="fas fa-search"></span>&nbsp;'.Cux::translate("core.dataProvider", "Sort by / filter", array(), "Mobile version of the data provider").'</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">';
            $str .= $this->renderMobileHead($columns, $id);
            $str .= '</div>';
            $str .= '<div class="modal-footer">';
            $str .= CuxHTML::button(CuxHTML::tag("span", "", array("class" => "fas fa-search"))."&nbsp;".Cux::translate("core.dataProvider", "Search", array(), "Mobile version of the data provider"), array("class" => "btn btn-sm btn-success", "onclick" => "doSearch_{$id}()"));
            $str .= "&nbsp;";
            $str .= CuxHTML::button(CuxHTML::tag("span", "", array("class" => "fas fa-redo"))."&nbsp;".Cux::translate("core.dataProvider", "Reset", array(), "Mobile version of the data provider"), array("class" => "btn btn-sm btn-info", "onclick" => "clearSearch_{$id}()"));
            $str .= "&nbsp;";
            $str .= CuxHTML::button(CuxHTML::tag("span", "", array("class" => "fas fa-times"))."&nbsp;".Cux::translate("core.dataProvider", "Close", array(), "Mobile version of the data provider"), array("class" => "btn btn-sm btn-danger", "onclick" => "", "data-dismiss" => "modal"));
            $str .= '</div>';
            $str .= '</div>';
            $str .= '</div>';
            $str .= "</div>";
            $str .= "</th></tr>";
        }
        $str .= "</thead>";
        $str .= "<tbody>";
        if (!empty($this->_data)) {
            foreach ($this->_data as $row) {
                if (!$isMobile) {
                    $str .= "<tr>";
                    foreach ($columns as $key => $columnDetails) {
                        $str .= "<td>";
                        if (is_string($columnDetails["value"])) {
                            $str .= $row->getAttribute($columnDetails["value"]);
                        } elseif (is_callable($columnDetails["value"])) {
                            $str .= call_user_func($columnDetails["value"], $row);
                        } else {
                            $str .= " - ";
                        }
                        $str .= "</td>";
                    }
                    $str .= "</tr>";
                } else {
                    $str .= "<tr><td>";
                    foreach ($columns as $key => $columnDetails) {
                        if (is_string($columnDetails["value"])) {
                            $str .= $row->getAttribute($columnDetails["value"]);
                        } elseif (is_callable($columnDetails["value"])) {
                            $str .= call_user_func($columnDetails["value"], $row);
                        } else {
                            $str .= " - ";
                        }
                    }
                    $str .= "</td></tr>";
                }
            }
        } else {
            $str .= "<tr>";
            $colspan = $isMobile ? 1 : count($columns);
            $str .= "<td colspan='" . $colspan . "'>";
            $str .= "<div class='alert alert-info'>" . Cux::translate("core.dataProvider", "No data to show", array(), "No data to show. No results found.") . "</div>";
            $str .= "</td>";
            $str .= "</tr>";
        }
        $str .= "</tbody>";
        $str .= "</table>";

        if ($this->getShowFilter()) {
            $str .= $this->renderJS($id, $columns);
        }

        return $str;
    }

    public function render(): string {

        $ret = "";

        $replace = array(
            "{filter}" => $this->renderFilter(),
            "{pager}" => $this->renderPager(),
            "{summary}" => $this->renderSummary(),
            "{header}" => $this->renderHeader(),
            "{list}" => $this->renderList(),
            "{footer}" => $this->renderFooter(),
            "{page}" => $this->_pager->getPage(),
            "{totalPages}" => $this->_pager->getTotalPages(),
            "{totalResults}" => $this->_pager->getTotalResults()
        );

        return str_replace(array_keys($replace), $replace, $this->getTemplate());
    }

}
