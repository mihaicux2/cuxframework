<?php

/**
 * CuxDBCriteria class file
 * 
 * @package Components
 * @subpackage DB
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\db;

/**
 * Simple class that acts as a database search condition wrapped
 */
class CuxDBCriteria{
    
    /**
     * The database search condition(s)
     * @var string
     */
    public $condition;
    
    /**
     * The list of parameters to be used by PDO, in order to prevent SQL injection
     * @var array
     */
    public $params;
    
    /**
     * Database order criteria
     * @var string 
     */
    public $order;
    
    /**
     * Database results limit
     * @var int
     */
    public $limit = 500;
    
    /**
     * Database results offset ( how many records to skip from the result)
     * @var int
     */
    public $offset = 0;
    
    /**
     * The list of columns to be returned by a database query
     * @var array
     */
    public $select = array();
    
    /**
     * The list of tables to be joined
     * @var array
     */
    public $join = array();
    
    /**
     * Class constructor
     */
    public function __construct() {
        $this->addCondition("1=1");
    }
    
    /**
     * Add SQL conditions for PDO statements
     * @param string $cond
     * @param string $defaultOperator
     */
    public function addCondition(string $cond, string $defaultOperator = "AND"){
        $this->condition = (!empty($this->condition)) ? ($this->condition." ".$defaultOperator." (".$cond.")") : "(".$cond.")";
    }
    
    /**
     * Add SQL IN condition for PDO statements
     * @param string $column
     * @param array $values
     * @param string $defaultOperator
     */
    public function addInCondition(string $column, array $values, string $defaultOperator = "AND"){
        $ops = array();
        $params = array();
        
        // borderline condition :)
        if (empty($values)){
            $values[] = -1;
        }
        
        foreach ($values as $i => $value){
            $param = ":".$this->paramName().$i;
            $params[] = $param;
            $this->params[$param] = $value;
        }
        
        $this->addCondition($column." IN (".implode(",", $params).")", $defaultOperator);
    }
    
    /**
     * Generate random strings for PDO parameter binding
     * @param int $length
     * @return string
     */
    public function paramName(int $length=5): string{
        return mb_substr(str_shuffle(md5(mt_srand())), 0, min($length, 16));
    }
    
}

