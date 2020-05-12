<?php

namespace CuxFramework\utils;

use CuxFramework\utils\Cux;
use CuxFramework\components\db\CuxDBObject;
use CuxFramework\components\db\CuxDBCriteria;

class CuxActiveDataProvider extends CuxDataProvider {
    
    private $_model;
    private $_pager;
    private $_sorter;
    private $_criteria;
    
    private $_data;
    
    public function __construct(CuxDbObject $model, array $options = array()) {
        $this->_model = $model;
        parent::__construct($options);
        
        if (!isset($options["columns"])){
            $columns = array();
            foreach ($this->_model->getAttributes() as $key => $val){
                $columns[$key] = array(
                    "key" => $key,
                    "value" => $key,
                    "label" => $this->_model->getLabel($key)
                );
            }
            $this->setColumns($columns);
        }
        
        if (isset($options["pager"]) && $options["pager"] instanceof CuxBasePaginator){
            $this->setPager($options["pager"]);
        } else {
            $pager = new CuxPaginator(1, 1);
            $this->setPager($pager);
        }
        
        if (isset($options["sorter"]) && $options["sorter"] instanceof CuxSorter){
            $this->setSorter($options["sorter"]);
        } else {
            $sorter = new CuxSorter($model);
            $sorter->setMultiSort(true);
            $pks = $this->_model->getPk();
            $keys = array_keys($pks);
            $sorter->setDefaultSortOrder($keys[0], "asc");
            $this->setSorter($sorter);
        }
        
        if (isset($options["criteria"]) && $options["criteria"] instanceof CuxDBCriteria){
            $this->setCriteria($options["criteria"]);
        } else {
            $this->setCriteria(new CuxDBCriteria());
        }
        
        $params = Cux::getInstance()->getActionParams();
        $pageParam = $this->_pager->getPageParam();
        $page = (int) (isset($params[$pageParam]) ? $params[$pageParam] : Cux::getInstance()->request->getParam($pageParam, 1));
        if (!$page){
            $page = 1;
        }
        
        $total = $this->_model->countAllByCondition($this->_criteria);
        $totalPages = ceil(($total > 0) ? $total / self::ROWS_PER_PAGE : 1);
        
        if ($page > $totalPages){
            $page = $totalPages;
        }
        
        $this->_pager->setTotalPages($totalPages);
        $this->_pager->setPage($page);
        
        $this->_criteria->limit = self::ROWS_PER_PAGE;
        $this->_criteria->offset = self::ROWS_PER_PAGE * ($page-1);
        
        $this->_sorter->applyCriteria($this->_criteria);
        
        $this->_data = $this->_model->findAllByCondition($this->_criteria);
        
    }
    
    public function setCriteria(CuxDBCriteria $criteria){
        $this->_criteria = $criteria;
    }
    
    public function getCriteria(): CuxDBCriteria{
        return $this->_criteria;
    }
    
    public function setPager(CuxBasePaginator $pager){
        $this->_pager = $pager;
    }
    
    public function getPager(): CuxBasePaginator{
        return $this->_pager;
    }
    
    public function setSorter(CuxSorter $sorter){
        $this->_sorter = $sorter;
    }
    
    public function getSorter(): CuxSorter {
        return $this->_sorter;
    }
    
    private function getLabels(): array{
        return $this->_model->labels();
    }
    
    public function render(): string {
        
        ob_start();
        echo $this->_pager->render();
        echo $this->getHeader();
        $columns = $this->getColumns();
        ?>
        <table class="<?php echo $this->getTableClass(); ?>">
            <thead class="<?php echo $this->getTHeadClass(); ?>">
                <tr>
                    <?php foreach ($columns as $key => $columnDetails): ?>
                        <?php $width = isset($columnDetails["width"]) ? " width='".$columnDetails["width"]."'" : ""; ?>
                        <th <?php echo $width; ?>><?php echo $this->_sorter->sortLink($key, $columnDetails["label"]); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($this->_data)): ?>
                <?php foreach ($this->_data as $row): ?>
                <tr>
                <?php foreach ($columns as $key => $columnDetails): ?>
                    <td>
                        <?php // print_r($columnDetails); ?>
                        <?php if (is_string($columnDetails["value"])): ?>
                            <?php echo $row->getAttribute($columnDetails["value"]); ?>
                        <?php elseif (is_callable($columnDetails["value"])): ?>
                            <?php echo call_user_func($columnDetails["value"], $row); ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="<?php echo count($columns); ?>">
                        <div class="alert alert-info"><?php echo Cux::translate("core.dataProvider", "No data to show"); ?></div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
        echo $this->getFooter();
        echo $this->_pager->render();
        return ob_get_clean();
        
//        return print_r($this->_data, true);
        
    }

}