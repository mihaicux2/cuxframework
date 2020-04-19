<?php

namespace CuxFramework\components\db;

use CuxFramework\utils\Cux;
use CuxFramework\utils\CuxObject;
use PDO;

abstract class CuxDBObject extends CuxObject {

    const HAS_ONE = 1; // current relation columns must match the related object's primary key
    const BELONGS_TO = 2; // current object primary key must match the related object's columns
    const HAS_MANY = 3; // current object primary key must match the related object's columns

    protected $_relations = array();
    protected $_with = array();
    protected $_isNewRecord = true;

    abstract public static function tableName(): string;

    abstract public function relations(): array;

    private static $_instances = array();
    
    private $dbConnection;
    
    public function __sleep()
    {
        return array('_attributes', '_errors', '_hasErrors', '_relations', '_with', '_isNewRecord');
    }
    
    /**
     * This method can be overridden by extending classes. It's useful if you want to have both master and slave connections
     * @return \CuxFramework\components\db\PDOWrapper
     */
    public function getDBConnection(): PDOWrapper{
        if (!$this->dbConnection) {
            $this->dbConnection = Cux::getInstance()->db;
        }
        return $this->dbConnection;
    }
    
    public function setDBConnection(PDOWrapper $dbConnection): CuxDBObject {
        $this->dbConnection = $dbConnection;
        return $this;
    }
    
    public function __construct() {
        $columnMap = $this->getTableSchema();

        foreach ($columnMap["columns"] as $column => $props) {
            $this->_attributes[$column] = isset($props["defaultValue"]) ? $props["defaultValue"] : "";
        }
    }

    final public static function getInstance(): CuxDBObject {
        $calledClass = get_called_class();

        if (!isset(self::$_instances[$calledClass])) {
            self::$_instances[$calledClass] = new $calledClass();
        }

        return self::$_instances[$calledClass];
    }

    public function __get(string $name) {
        try {
            return parent::__get($name);
        } catch (\Exception $ex) {
            if ($this->hasRelation($name)) {
                return $this->getRelation($name);
            }
        }
        throw new \Exception(Cux::translate("error", "Undefined property: {class}.{attribute}", array(
            "{class}" => get_class($this),
            "{attribute}" => $name
        )), 503);
    }

    public function __set(string $name, $value) {
        try {
            parent::__set($name, $value);
        } catch (\Exception $ex) {
            if ($this->hasRelation($name) && is_subclass_of($value, $this->getRelationClassName($name))) {
                $this->_relations[$name] = $value;
            } else {
                throw new \Exception(Cux::translate("error", "Undefined property: {class}.{attribute}", array(
                    "{class}" => get_class($this),
                    "{attribute}" => $name
                )), 503);
            }
        }
    }

    public function __isset(string $name) {
        if (!parent::__isset($name)) {
            return isset($this->_relations[$name]);
        }
        return true;
    }

    public function __unset(string $name) {
        try {
            parent::__unset($name);
        } catch (\Exception $ex) {
            if (isset($this->_relations[$name])) {
                $this->_relations[$name] = null;
            } else {
                throw new \Exception(Cux::translate("error", "Undefined property: {class}.{attribute}", array(
                    "{class}" => get_class($this),
                    "{attribute}" => $name
                )), 503);
            }
        }
    }

    public function getRelationClassName(string $name): string {
        $relations = $this->relations();
        return isset($relations[$name]) ? $relations[$name]["class"] : null;
    }

    public function hasRelation(string $related): bool {
        if (isset($this->_relations[$related])) {
            return true;
        } else {
            $relations = $this->relations();
            if (array_key_exists($related, $relations)) {
                return true;
            }
        }
        return false;
    }

    public function setAttribute(string $attribute, $value): CuxObject {
        $columnMap = $this->getTableSchema();
        if (isset($columnMap["columns"][$attribute])) {
            return parent::setAttribute($attribute, $value);
        }
        return null;
    }

    public function with(array $rels = null): CuxDBObject {
        if (is_null($rels) || empty($rels)){
            return $this;
        }
        
        $relations = $this->relations();
        foreach ($rels as $relation) {

            $relatedParts = explode(".", $relation);
            $relationsPath = array(
                array(
                    "class" => $relations[$relatedParts[0]]["class"],
                    "relations" => $relations[$relatedParts[0]]["class"]::getInstance()->relations()
                )
            );
            $relatedParts = array_slice($relatedParts, 1);
            foreach ($relatedParts as $depth => $related2) {
                if (!isset($relationsPath[$depth]["relations"][$related2])) {                    
                    throw new \Exception(Cux::translate("error", "Undefined property: {class}.{attribute}", array(
                        "{class}" => $relationsPath[$depth]["class"],
                        "{attribute}" => $related2
                    )), 503);
                    
                }
                $relationsPath[$depth + 1] = array(
                    "class" => $relationsPath[$depth]["relations"][$related2]["class"],
                    "relations" => $relationsPath[$depth]["relations"][$related2]["class"]::getInstance()->relations()
                );
            }
            $this->_with[$relation] = $relationsPath;
        }

        return $this;
    }

    public function getPDOType($type){
        switch ($type){
            case "integer":
                return PDO::PARAM_INT;
                break;
            case "string":
                return PDO::PARAM_STR;
                break;
            default:
                return false;
        }
    }
    
    public function getByPk($key): CuxDBObject {

        $columnMap = $this->getTableSchema();
        $pk = $columnMap["primaryKey"];
        $conditions = array();
        if (is_array($key)) {
            foreach ($key as $i => $pkVal) {
                $param = ":" . $i;
                $params[$param] = $pkVal;
                $conditions[$param] = $this->getDbConnection()->quoteTableName($i) . "=" . $param;
            }
        } else {
            $param = ":" . $pk[0];
            $params[$param] = $key;
            $conditions[$param] = $this->getDbConnection()->quoteTableName($pk[0]) . "=" . $param;
        }

        $query = "SELECT " . implode(", ", $this->getColumnsForQuery())
                . " FROM " . $this->getDbConnection()->quoteTableName(static::tableName())
                . " WHERE ( " . implode(" ) AND ( ", $conditions) . " )"
                . " LIMIT 1"; // if the key is really primary, than there cannot be multiple records with the same value(/s)        

        $stmt = $this->getDbConnection()->prepare($query);
        foreach ($params as $key2 => $value) {
            $stmt->bindValue($key2, $value, $this->getDbConnection()->getPDOType($columnMap["columns"][substr($key2, 1)]["type"]));
        }

        if ($stmt->execute()) {
            $row = $stmt->fetch();
            if (!$row) {
                return null;
            }
            $calledClass = get_called_class();
            $ob = new $calledClass();
            $ob->setAttributes($row);
            $ob->_isNewRecord = false;
            return $ob;
        }

        return null;
    }

    public function isNewRecord() {
        return $this->_isNewRecord;
    }

    protected function getTableSchema() {
        return $this->getDbConnection()->getTableSchema($this->tableName());
    }

    protected function getColumnMap() {
        return $this->getDbConnection()->getColumnMap($this->tableName());
    }

    protected function quoteValue($name) {
        return strpos($name, "'") !== false ? $name : "'" . $name . "'";
    }

    public function getRelation($related) {
        if (isset($this->_relations[$related])) {
            return $this->_relations[$related];
        } else {
            $relations = $this->relations();
            if (isset($relations[$related])) {
                $ob = $relations[$related]["class"];
                $crit = new CuxDBCriteria();
                if (isset($relations[$related]["via"])){
                    $obInstance = $ob::getInstance();
                    $pk = $ob::getInstance()->getPk();
                    $pkKeys = array_keys($pk);
                    $where =  (isset($relations[$related]["via"]["condition"])) ? (" AND ".$relations[$related]["via"]["condition"]) : "";
                    $crit->join[] = "JOIN ".$relations[$related]["via"]["table"]." t1 ON t1.".$relations[$related]["via"]["keys"]["to"]." = ".$ob::getInstance()->tableName().".".$pkKeys[0].$where;
                }
                if (isset($relations[$related]["condition"])){
                    $crit->addCondition($relations[$related]["condition"]);
                }
                
                switch ($relations[$related]["type"]) {
                    case static::HAS_ONE:
                        $relPk = $ob::getInstance()->getPkName();
                        $crtPk = $this->getPk();
                        $i = 0;
                        foreach ($crtPk as $key => $pk) {
                            $param = ":" . $crit->paramName();
                            $crit->addCondition($ob::getInstance()->tableName() . "." . $relations[$related]["key"][$i] . "=" . $param);
                            $crit->params[$param] = $pk;
                            $i++;
                        }
                        if (isset($relations[$related]["orderBy"])) {
                            $crit->order = $relations[$related]["orderBy"];
                        } else {
                            $crit->order = $relations[$related]["key"][0] . " DESC";
                        }
                        $x = $ob::getInstance()->findByCondition($crit);
                        $this->_relations[$related] = $ob::getInstance()->findByCondition($crit);
                        break;
                    case static::BELONGS_TO:
                        $relPk = $ob::getInstance()->getPkName();
                        $crtPk = $this->getPk();
                        $i = 0;
                        foreach ($crtPk as $key => $pk) {
                            $param = ":" . $crit->paramName();
                            $crit->addCondition($ob::getInstance()->tableName() . "." . $relPk[$i] . "=" . $param);
                            $crit->params[$param] = $this->getAttribute($relations[$related]["key"][$i]);
                            $i++;
                        }
                        if (isset($relations[$related]["orderBy"])) {
                            $crit->order = $relations[$related]["orderBy"];
                        } else {
                            $crit->order = $relPk[0] . " DESC";
                        }
                        $this->_relations[$related] = $ob::getInstance()->findByCondition($crit);
                        break;
                    case static::HAS_MANY:
                        $relPk = $ob::getInstance()->getPkName();
                        $crtPk = $this->getPk();
                        $i = 0;
                        foreach ($crtPk as $key => $pk) {
                            $param = ":" . $crit->paramName();
                            if (isset($relations[$related]["via"])){
                                $crit->addCondition("t1." . $relations[$related]["key"][$i] . "=" . $param);
                            } else {
                                $crit->addCondition($ob::getInstance()->tableName() . "." . $relations[$related]["key"][$i] . "=" . $param);
                            }
                            $crit->params[$param] = $pk;
                            $i++;
                        }
                        if (isset($relations[$related]["orderBy"])) {
                            $crit->order = $relations[$related]["orderBy"];
                        } else {
                            $crit->order = $relations[$related]["key"][0] . " DESC";
                        }
                        $this->_relations[$related] = $ob::getInstance()->findAllByCondition($crit);
                        break;
                    default:
                        throw new \Exception(Cux::translate("error", "Undefined relation: {class}.{attribute}", array(
                            "{class}" => get_class($this),
                            "{attribute}" => $related
                        )), 503);
                }
                return $this->_relations[$related];
            }
        }
        return false;
    }

    protected function getColumnsForQuery($alias = FALSE) {
        $columnMap = $this->getTableSchema();
        $cols2 = array_keys($columnMap["columns"]);
        $cols = array();
        $table = $alias != FALSE ? $this->getDbConnection()->quoteTableName($alias) : $this->getDbConnection()->quoteTableName($this->tableName());
        foreach ($cols2 as $col) {
            $cols[] = $table . "." . $this->getDbConnection()->quoteTableName($col);
        }
//        if (!empty($this->_with)) {
//            $relations = $this->relations();
//            foreach ($this->_with as $i => $related) {
//                if (strpos($related, ".")){
//                    $op = explode(".", $related);
//                    if (isset($relations[$op[0]])){
//                        $ob = new $relations[$op[0]]["class"]();
//                        $relations2 = $ob->relations();
//                        $ob2 = $relations2[$op[1]]["class"];
//                        $cols = array_merge($cols, $ob2::getInstance()->getColumnsForQuery($op[1]));
//                    }
//                }
//                else{
//                    $ob = $relations[$related]["class"];
//                    $cols = array_merge($cols, $ob::getInstance()->getColumnsForQuery($related));
//                }
//            }
//        }
//        print_r($cols);
//        die();
        return $cols;
    }

    protected function insert($fields = array()): bool {
        if (!$this->beforeInsert($fields))
            return false;

        $columnMap = $this->getTableSchema();
        $pk = $columnMap["primaryKey"];
        $columns = array();
        $binds = array();

        $hasCustomFields = !empty($fields);
        if ($hasCustomFields) {
            $fields = array_flip($fields);
        }

        foreach ($this->_attributes as $column => $value) {
            if (($hasCustomFields && !isset($fields[$column])))
                continue;
            if (!$value && ($columnMap["columns"][$column]["isPrimaryKey"] || $columnMap["columns"][$column]["allowNull"]))
                continue;
            $columns[] = $this->getDbConnection()->quoteSimpleTableName($column) . "=:" . $column;
            $binds[":" . $column] = $value;
        }

        if (empty($columns)) {
            return false;
        }

        $query = "INSERT INTO " . $this->getDbConnection()->quoteTableName(static::tableName())
                . " SET " . implode(", ", $columns) . "";

        $stmt = $this->getDbConnection()->prepare($query);
        foreach ($binds as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        try {
            if ($stmt->execute()) {
                $this->_attributes[$pk[0]] = $this->getDbConnection()->lastInsertId();
                $this->afterInsert();
                return true;
            }
        } catch (\PDOException $ex) {
            throw new \Exception($ex->getMessage(), (int) $ex->getCode());
        }

        return false;
    }

    protected function update($fields = array()): bool {
        if (!$this->beforeUpdate($fields))
            return false;

        $columnMap = $this->getTableSchema();
        $pk = $this->getPk();

        $conditions = array();
        if (!empty($pk)) {
            foreach ($pk as $key => $value) {
                $param = ":" . $key;
                $params[$param] = $value;
                $conditions[$param] = $this->getDbConnection()->quoteTableName($key) . "=" . $param;
            }
        } else {
            $conditions[] = "1=1";
        }

        $values = array();
        $hasCustomFields = !empty($fields);
        if ($hasCustomFields) {
            $fields = array_flip($fields);
        }

        foreach ($this->_attributes as $column => $value) {
            if (($hasCustomFields && !isset($fields[$column])))
                continue;
            if (!$value && ($columnMap["columns"][$column]["isPrimaryKey"]))
                continue;
            $values[] = $this->getDbConnection()->quoteSimpleTableName($column) . "=:" . $column;
            $params[":" . $column] = $value;
        }

        // nothing to save
//        if (empty($params)){
//            return false;
//        }

        $query = "UPDATE " . $this->getDbConnection()->quoteTableName(static::tableName())
                . " SET " . implode(", ", $values)
                . " WHERE ( " . implode(" ) AND ( ", $conditions) . " )"
                . " LIMIT 1";

        $stmt = $this->getDbConnection()->prepare($query);
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
        }

        try {
            if ($stmt->execute()) {
                $this->afterUpdate($fields);
                return true;
            }
        } catch (\PDOException $ex) {
            throw new \Exception($ex->getMessage(), (int) $ex->getCode());
        }

        return false;
    }

    public function save($fields = array()) {
        return $this->isNewRecord() ? $this->insert($fields) : $this->update($fields);
    }

    public function delete(): bool {
        if (!$this->beforeDelete())
            return false;

        $pk = $this->getPk();
        $conditions = array();
        foreach ($pk as $key => $value) {
            $param = ":" . $key;
            $params[$param] = $value;
            $conditions[$param] = $this->getDbConnection()->quoteTableName($key) . "=" . $param;
        }

        $query = "DELETE FROM " . $this->getDbConnection()->quoteTableName(static::tableName())
                . " WHERE ( " . implode(" ) AND ( ", $conditions) . " )"
                . " LIMIT 1";

        $stmt = $this->getDbConnection()->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        try {
            if ($stmt->execute()) {
                $this->afterDelete();
                return true;
            }
        } catch (\PDOException $ex) {
            throw new \Exception($ex->getMessage(), (int) $ex->getCode());
        }

        return false;
    }

    public function beforeInsert($fields = array()) {
        return true;
    }

    public function afterInsert($fields = array()) {
        $this->_isNewRecord = false;
    }

    public function beforeUpdate($fields = array()) {
        return true;
    }

    public function afterUpdate($fields = array()) {
        
    }

    public function beforeDelete(): bool {
        return true;
    }

    public function afterDelete() {
        
    }

    public function getPkName(): array {
        $columnMap = $this->getTableSchema();
        return $columnMap["primaryKey"];
    }

    public function getPk(): array {
        $columnMap = $this->getTableSchema();
        $pk = $columnMap["primaryKey"];
        $ret = array();
        foreach ($pk as $col) {
            $ret[$col] = $this->_attributes[$col];
        }
        return $ret;
    }

    public function findByAttributes(array $attributes): CuxDBObject {

        $conditions = array();
        foreach ($attributes as $column => $value) {
            $param = ":" . $column;
            $params[$param] = $value;
            $conditions[$param] = $this->getDbConnection()->quoteTableName($column) . "=" . $param;
        }

        $columnMap = $this->getTableSchema();

        $query = "SELECT " . implode(", ", $this->getColumnsForQuery())
                . " FROM " . $this->getDbConnection()->quoteTableName(static::tableName())
                . " WHERE ( " . implode(" ) AND ( ", $conditions) . " )"
                . " LIMIT 1";

        $stmt = $this->getDbConnection()->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, $this->getPDOType($columnMap["columns"][substr($key, 1)]["type"]));
        }

        if ($stmt->execute()) {
            $row = $stmt->fetch();
            if (!$row) {
                return null;
            }
            $calledClass = get_called_class();
            $ob = new $calledClass();
            $ob->setDBConnection($this->getDBConnection());
            $ob->setAttributes($row);
            $ob->_isNewRecord = false;
            return $ob;
        }

        return null;
    }

    public function findAllByAttributes(array $attributes): array {

        $ret = array();

        $conditions = array();
        foreach ($attributes as $column => $value) {
            $param = ":" . $column;
            $params[$param] = $value;
            $conditions[$param] = $this->getDbConnection()->quoteTableName($column) . "=" . $param;
        }

        $query = "SELECT " . implode(", ", $this->getColumnsForQuery())
                . " FROM " . $this->getDbConnection()->quoteTableName(static::tableName())
                . " WHERE ( " . implode(" ) AND ( ", $conditions) . " )";

        $columnMap = $this->getTableSchema();

        $stmt = $this->getDbConnection()->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, $this->getPDOType($columnMap["columns"][substr($key, 1)]["type"]));
        }

        if ($stmt->execute()) {
            $rows = $stmt->fetchAll();
            if (!$rows) {
                return array();
            }
            foreach ($rows as $row) {
                $calledClass = get_called_class();
                $ob = new $calledClass();
//                $ob->setDBConnection($this->getDBConnection());
                $ob->setAttributes($row);
                $ob->_isNewRecord = false;
                $ret[] = $ob;
            }
        }

        return $ret;
    }

    /**
     * @param mixed $condition
     */
    public function findByCondition(CuxDBCriteria $crit = null): CuxDBObject {

        if (is_null($crit)){
            $crit = new CuxDBCriteria();
        }
        
        $columnMap = $this->getTableSchema();

        $query = "SELECT " . implode(", ", $crit->select ? $crit->select : $this->getColumnsForQuery())
                . " FROM " . $this->getDbConnection()->quoteTableName(static::tableName());
        
        if (!empty($crit->join)){
            $query .= " ".implode(" ", $crit->join);
        }
        
        $query .=  " WHERE " . $crit->condition;
        if ($crit->order) {
            $query .= " ORDER BY " . $crit->order;
        }
        $query .= " LIMIT " . $crit->limit . " OFFSET " . $crit->offset;

        $stmt = $this->getDbConnection()->prepare($query);

        if (!empty($crit->params)) {
            foreach ($crit->params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
        }

        if ($stmt->execute()) {
            $row = $stmt->fetch();
            if (!$row) {
                return null;
            }
            $calledClass = get_called_class();
            $ob = new $calledClass();
//            $ob->setDBConnection($this->getDBConnection());
            $ob->setAttributes($row);
            $ob->_isNewRecord = false;
            return $ob;
        }

        return null;
    }

    public function countAllByCondition(CuxDBCriteria $crit): int {
        $ret = 0;

        $query = "SELECT COUNT(*) AS total"
                . " FROM " . $this->getDbConnection()->quoteTableName(static::tableName());
        
        if (!empty($crit->join)){
            $query .= " ".implode(" ", $crit->join);
        }
        
        $query .= " WHERE " . $crit->condition;

        $stmt = $this->getDbConnection()->prepare($query);

        if (!empty($crit->params)) {
            foreach ($crit->params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
        }

        if ($stmt->execute()) {
            $row = $stmt->fetch();
            $ret = $row["total"];
        }

        return $ret;
    }

    public function findAllByCondition(CuxDBCriteria $crit = null): array {

        if (is_null($crit)){
            $crit = new CuxDBCriteria();
        }
        
        $columnMap = $this->getTableSchema();

        $ret = array();

        $query = "SELECT " . implode(", ", $this->getColumnsForQuery())
                . " FROM " . $this->getDbConnection()->quoteTableName(static::tableName());
        
        if (!empty($crit->join)){
            $query .= " ".implode(" ", $crit->join);
        }

        $query .= " WHERE " . $crit->condition;
        if ($crit->order) {
            $query .= " ORDER BY " . $crit->order;
        }
        $query .= " LIMIT " . $crit->limit . " OFFSET " . $crit->offset;
        
        $stmt = $this->getDbConnection()->prepare($query);
        if (!empty($crit->params)) {
            foreach ($crit->params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
        }

        if ($stmt->execute()) {
            $rows = $stmt->fetchAll();

            if (!$rows) {
                return array();
            }

            $keys = array();

            foreach ($rows as $row) {
                $calledClass = get_called_class();
                $ob = new $calledClass();
//                $ob->setDBConnection($this->getDBConnection());
                $ob->setAttributes($row);
                $ob->_isNewRecord = false;
                $pk = $ob->getPk();
                $valid = false;
                foreach ($pk as $key1 => $value) {
                    if (!empty($value)) {
                        $valid = true;
                        break;
                    }
                }
                if (!$valid)
                    continue;
                $pkKeys = array_keys($pk);
                $keys[] = $key = $pk[$pkKeys[0]];
                if (!isset($ret[$key])) {
                    $ret[$key] = $ob;
                }
            }

            if (!empty($this->_with)) {

                $relations = $this->relations();

                foreach ($this->_with as $related => $relationsPath) {


                    if (strpos($related, ".") === FALSE) {

                        $ob = $relations[$related]["class"];
                        $relPk = $ob::getInstance()->getPkName();
                        $crtPk = $this->getPkName();

                        $crit2 = new CuxDBCriteria();

                        switch ($relations[$related]["type"]) {
                            case static::HAS_ONE:
                                $crit2->addInCondition($relations[$related]["key"][0], $keys);
                                if (isset($relations[$related]["orderBy"])) {
                                    $crit2->order = $relations[$related]["orderBy"];
                                } else {
                                    $crit2->order = $relations[$related]["key"][0] . " DESC";
                                }

                                $relatedProp = $relations[$related]["key"][0];

                                $relatedObjs = $ob::getInstance()->findByCondition($crit2);
                                if ($relatedObjs) {
                                    foreach ($relatedObjs as $relObPk => $relatedOb) {
                                        $ret[$relatedProp]->_relations[$related] = $relatedOb;
                                    }
                                }

                                break;
                            case static::BELONGS_TO:
                                foreach ($ret as $pkTmp => $ob1) {
                                    if ($ob1->hasAttribute($relations[$related]["key"][0])) {
                                        $keys2[] = $ob1->getAttribute($relations[$related]["key"][0]);
                                    }
                                }
                                $crit2->addInCondition($relPk[0], $keys2);
                                if (isset($relations[$related]["orderBy"])) {
                                    $crit2->order = $relations[$related]["orderBy"];
                                } else {
                                    $crit2->order = $relPk[0] . " DESC";
                                }

                                $relatedProp = $crtPk[0];
                                $relatedObjs = $ob::getInstance()->findAllByCondition($crit2);

                                if ($relatedObjs) {
                                    foreach ($ret as $pkTmp => $ob1) {
                                        if ($ob1->hasAttribute($relations[$related]["key"][0]) && isset($relatedObjs[$ob1->getAttribute($relations[$related]["key"][0])])) {
                                            $ret[$pkTmp]->_relations[$related] = $relatedObjs[$ob1->getAttribute($relations[$related]["key"][0])];
                                        }
                                    }
                                }
                                break;
                            case static::HAS_MANY:
                                $crit2->addInCondition($relations[$related]["key"][0], $keys);
                                if (isset($relations[$related]["orderBy"])) {
                                    $crit2->order = $relations[$related]["orderBy"];
                                } else {
                                    $crit2->order = $relations[$related]["key"][0] . " DESC";
                                }

                                $relatedProp = $relations[$related]["key"][0];
                                $relatedObjs = $ob::getInstance()->findAllByCondition($crit2);
                                if ($relatedObjs) {
                                    foreach ($relatedObjs as $relObPk => $relatedOb) {
                                        if ($relatedOb->hasAttribute($relatedProp) && isset($ret[$relatedOb->$relatedProp])) {
                                            $ret[$relatedOb->$relatedProp]->_relations[$related][$relObPk] = $relatedOb;
                                        }
                                    }
                                }

                                break;
                        }
                    }
                }
            }
        }

        $this->_with = array(); // sad, sad... :-<

        return $ret;
    }

    public function findAllByCondition2(CuxDBCriteria $crit = null) {

        if (is_null($crit)){
            $crit = new CuxDBCriteria();
        }
        
        $columnMap = $this->getTableSchema();

        $ret = array();

        $query = "SELECT " . implode(", ", $this->getColumnsForQuery())
                . " FROM " . $this->getDbConnection()->quoteTableName(static::tableName());

        if (!empty($this->_with)) {
            $relations = $this->relations();
            foreach ($this->_with as $i => $related) {
                if (strpos($related, ".") === FALSE) {
                    $ob = $relations[$related]["class"];
                    $query .= " LEFT JOIN " . $this->getDbConnection()->quoteTableName($ob::getInstance()->tableName()) . " AS " . $this->getDbConnection()->quoteTableName($related) . " ON ";
                    $relPk = $ob::getInstance()->getPkName();
                    $crtPk = $this->getPkName();
                    $opts = array();

                    switch ($relations[$related]["type"]) {
                        case static::HAS_ONE:
                            $i = 0;
                            foreach ($crtPk as $i => $pk) {
                                $opts[] = $related . "." . $relations[$related]["key"][$i] . "=" . $this->tableName() . "." . $pk;
                            }
                            break;
                        case static::BELONGS_TO:
                            foreach ($crtPk as $i => $pk) {
                                $opts[] = $related . "." . $relPk[$i] . "=" . $this->tableName() . "." . $relations[$related]["key"][$i];
                            }
                            break;
                        case static::HAS_MANY:
                            foreach ($crtPk as $i => $pk) {
                                $opts[] = $related . "." . $relations[$related]["key"][$i] . "=" . $this->tableName() . "." . $pk;
                            }
                            break;
                    }
                } else {
                    $op = explode(".", $related);
                    $ob = new $relations[$op[0]]["class"]();
                    $relations2 = $ob->relations();
                    $ob2 = new $relations2[$op[1]]["class"]();
                    $query .= " LEFT JOIN " . $this->getDbConnection()->quoteTableName($ob2::getInstance()->tableName()) . " AS " . $this->getDbConnection()->quoteTableName($op[1]) . " ON ";
                    $relPk = $ob2::getInstance()->getPkName();
                    $crtPk = $ob::getInstance()->getPkName();
                    $opts = array();

                    switch ($relations2[$op[1]]["type"]) {
                        case static::HAS_ONE:
                            $i = 0;
                            foreach ($crtPk as $i => $pk) {
                                $opts[] = $op[0] . "." . $relations2[$op[1]]["key"][$i] . "=" . $op[1] . "." . $pk;
                            }
                            break;
                        case static::BELONGS_TO:
                            foreach ($crtPk as $i => $pk) {
                                $opts[] = $op[0] . "." . $relations2[$op[1]]["key"][$i] . "=" . $op[1] . "." . $relPk[$i];
                            }
                            break;
                        case static::HAS_MANY:
                            foreach ($crtPk as $i => $pk) {
                                $opts[] = $op[0] . "." . $relations2[$op[1]]["key"][$i] . "=" . $op[1] . "." . $pk;
                            }
                            break;
                    }
                }

                $query .= implode(" AND ", $opts);
            }
        }

        $query .= " WHERE " . $crit->condition;
        if ($crit->order) {
            $query .= " ORDER BY " . $crit->order;
        }
        $query .= " LIMIT " . $crit->limit . " OFFSET " . $crit->offset;

        $stmt = $this->getDbConnection()->prepare($query);
        if (!empty($crit->params)) {
            foreach ($crit->params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
        }

        if ($stmt->execute()) {
            $rows = $stmt->fetchAll();

            if (!$rows) {
                return array();
            }
            foreach ($rows as $row) {
                $calledClass = get_called_class();
                $ob = new $calledClass();
                $ob->setAttributes($row);
                $ob->_isNewRecord = false;
                $pk = $ob->getPk();
                $valid = false;
                foreach ($pk as $key1 => $value) {
                    if (!empty($value)) {
                        $valid = true;
                        break;
                    }
                }
                if (!$valid)
                    continue;
                $key = implode("_", $pk);
                if (!isset($ret[$key])) {
                    $ret[$key] = $ob;
                }
                if (!empty($this->_with)) {
                    $relations = $this->relations();
                    foreach ($this->_with as $i => $related) {
                        if (strpos($related, ".") === FALSE) {
                            $ob2 = new $relations[$related]["class"]();
                            $ob2->setAttributes($row);
                            $pk2 = $ob2->getPk();
                            $valid2 = false;
                            foreach ($pk2 as $key2 => $value) {
                                if (!empty($value)) {
                                    $valid2 = true;
                                    break;
                                }
                            }
                            if (!$valid2)
                                continue;
                            $ob2->_isNewRecord = false;
                            if ($relations[$related]["type"] == static::HAS_MANY) {
                                $ret[$key]->_relations[$related][implode("_", $pk2)] = $ob2;
                            } else {
                                $ret[$key]->_relations[$related] = $ob2;
                            }
                        } else {
                            $op = explode(".", $related);
                            $ob2 = new $relations[$op[0]]["class"]();
                            $relations2 = $ob2->relations();
                            $ob3 = new $relations2[$op[1]]["class"]();
                            $ob2->setAttributes($row);
                            $ob3->setAttributes($row);
                            $pk2 = $ob2->getPk();
                            $pk3 = $ob3->getPk();

                            $valid2 = false;
                            foreach ($pk2 as $key1 => $value) {
                                if (!empty($value)) {
                                    $valid2 = true;
                                    break;
                                }
                            }
                            if (!$valid2)
                                continue;

                            $valid3 = false;
                            foreach ($pk3 as $key1 => $value) {
                                if (!empty($value)) {
                                    $valid3 = true;
                                    break;
                                }
                            }
                            if ($valid3) {
                                if ($relations2[$op[1]] == static::HAS_MANY) {
                                    $ob2->_relations[$op[1]][implode("_", $pk3)] = $ob3;
                                } else {
                                    $ob2->_relations[$op[1]] = $ob3;
                                }
                            }
                            $ob2->_isNewRecord = false;
                            $ob3->_isNewRecord = false;
                            if ($relations[$op[0]]["type"] == static::HAS_MANY) {
                                $ret[$key]->_relations[$op[0]][implode("_", $pk2)] = $ob2;
                            } else {
                                $ret[$key]->_relations[$op[0]] = $ob2;
                            }
                        }
                    }
                }
            }
        }

        $this->_with = array(); // sad, sad... :-<

        return $ret;
    }

    public function refresh() {
        if ($this->isNewRecord()) {
            return $this;
        }

        $new = $this->getByPk($this->getPk());
        $this->setAttributes($new->getAttributes());
        $new = null;
        unset($new);
        return $this;
    }
    
    public function arrayListByCondition(string $key, string $val, CuxDBCriteria $crit = null): array {

        if (is_null($crit)){
            $crit = new CuxDBCriteria();
        }

        $ret = array();

        $query = "SELECT " . $key . " AS `key`, ". $val . " AS `val`"
                     . " FROM " . $this->getDbConnection()->quoteTableName(static::tableName());
        
        if (!empty($crit->join)){
            $query .= " ".implode(" ", $crit->join);
        }

        $query .= " WHERE " . $crit->condition;
        if ($crit->order) {
            $query .= " ORDER BY " . $crit->order;
        }
        $query .= " LIMIT " . $crit->limit . " OFFSET " . $crit->offset;
        
        $stmt = $this->getDbConnection()->prepare($query);
        if (!empty($crit->params)) {
            foreach ($crit->params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
        }

        if ($stmt->execute()) {
            $rows = $stmt->fetchAll();

            if (!$rows) {
                return array();
            }

            $keys = array();

            foreach ($rows as $row) {
                $ret[$row["key"]] = $row["val"];
            }
        }

        $this->_with = array(); // sad, sad... :-<

        return $ret;
    }

}
