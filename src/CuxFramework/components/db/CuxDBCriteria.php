<?php

namespace CuxFramework\components\db;

class CuxDBCriteria{
    
    public $condition;
    public $params;
    public $order;
    public $limit = 500;
    public $offset = 0;
    public $select = array();
    public $join = array();
    
    public function __construct() {
        $this->addCondition("1=1");
    }
    
    public function addCondition(string $cond, string $defaultOperator = "AND"): void{
        $this->condition = (!empty($this->condition)) ? ($this->condition." ".$defaultOperator." (".$cond.")") : "(".$cond.")";
    }
    
    public function addInCondition(string $column, array $values, string $defaultOperator = "AND"): void{
        $ops = array();
        $params = array();
        foreach ($values as $i => $value){
            $param = ":".$this->paramName().$i;
            $params[] = $param;
            $this->params[$param] = $value;
        }
        $this->addCondition($column." IN (".implode(",", $params).")", $defaultOperator);
    }
    
    public function paramName(int $length=5): string{
        return mb_substr(str_shuffle(md5(mt_srand())), 0, min($length, 16));
    }
    
}

