<?php

namespace CuxFramework\components\db;

use PDO;
use PDOException;
use PDOStatement;

use CuxFramework\utils\Cux;
use CuxFramework\utils\CuxBaseObject;

class PDOWrapper  extends CuxBaseObject {

    const TYPE_PK = 'pk';
    const TYPE_UPK = 'upk';
    const TYPE_BIGPK = 'bigpk';
    const TYPE_UBIGPK = 'ubigpk';
    const TYPE_CHAR = 'char';
    const TYPE_STRING = 'string';
    const TYPE_TEXT = 'text';
    const TYPE_SMALLINT = 'smallint';
    const TYPE_INTEGER = 'integer';
    const TYPE_BIGINT = 'bigint';
    const TYPE_FLOAT = 'float';
    const TYPE_DOUBLE = 'double';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_DATETIME = 'datetime';
    const TYPE_TIMESTAMP = 'timestamp';
    const TYPE_TIME = 'time';
    const TYPE_DATE = 'date';
    const TYPE_BINARY = 'binary';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_MONEY = 'money';

    private $_db = null;
    private $_stmt = null;
    public $connectionString = "";
    public $username = "";
    public $password = "";
    public $enableSchemaCache = true;
    public $schemaCacheTimeout = 3600;
    public $fetchMode = PDO::FETCH_ASSOC;
    public $errorMode = PDO::ERRMODE_EXCEPTION;
    
    public function config(array $config) {
       parent::config($config);
        try {
            $this->_db = new PDO($this->connectionString, $this->username, $this->password);
            if (isset($this->fetchMode)) {
                $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $this->fetchMode);
            }
            if (isset($this->errorMode)) {
                $this->setAttribute(PDO::ATTR_ERRMODE, $this->errorMode);
            }
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage(), (int) $ex->getCode());
        }
    }

    public function beginTransaction(): bool {
        return $this->_db->beginTransaction();
    }

    public function commit(): bool {
        return $this->_db->commit();
    }

    public function rollBack(): bool {
        return $this->_db->rollBack();
    }

    public function exec(string $statement): int {
        return $this->_db->exec($statement);
    }

    public function errorCode(): string {
        return $this->_db->errorCode();
    }

    public function errorInfo(): array {
        return $this->_db->errorInfo();
    }

    /**
     * 
     * @param int $attribute
     * @return mixed
     */
    public function getAttribute(int $attribute) {
        return $this->_db->getAttribute($attribute);
    }

    public function inTransaction(): bool {
        return $this->_db->inTransaction();
    }

    public function lastInsertId($name = null): string {
        return $this->_db->lastInsertId($name);
    }

    public function prepare(string $statement, array $driver_options = array()): PDOStatement {
        return $this->_db->prepare($statement);
    }

    public function query(string $statement, array $driver_options = array()): PDOStatement {
        return $this->_db->query($statement);
    }

    public function quote(string $string, int $parameter_type = PDO::PARAM_STR): string {
        return $this->_db->quote($string, $parameter_type);
    }

    /**
     * 
     * @param int $attribute
     * @param mixed $value
     * @return bool
     */
    public function setAttribute(int $attribute, $value): bool {
        return $this->_db->setAttribute($attribute, $value);
    }

    public function quoteTableName(string $name): string {
        if (strpos($name, '(') !== false || strpos($name, '{{') !== false) {
            return $name;
        }
        if (strpos($name, '.') === false) {
            return $this->quoteSimpleTableName($name);
        }
        $parts = explode('.', $name);
        foreach ($parts as $i => $part) {
            $parts[$i] = $this->quoteSimpleTableName($part);
        }

        return implode('.', $parts);
    }

    public function quoteSimpleTableName(string $name): string {
        return strpos($name, "`") !== false ? $name : "`" . $name . "`";
    }

    public function getTableSchema(string $tableName): array {
        if (Cux::getInstance()->hasComponent("cache")) {
            $map = Cux::getInstance()->cache->get("schema." . $this->connectionString.".".$tableName);
            if ($map !== FALSE) {
                return $map;
            }
        }

        $schema = array(
            "primaryKey" => array()
        );
        $columns = $this->getColumnMap($tableName);

        foreach ($columns as $column) {
            $schema["columns"][$column["name"]] = $column;
            if ($column["isPrimaryKey"]) {
                $schema["primaryKey"][] = $column["name"];
            }
        }

        if (Cux::getInstance()->hasComponent("cache") && $this->enableSchemaCache) {
            Cux::getInstance()->cache->set("schema." . $this->connectionString.".".$tableName, $schema, $this->schemaCacheTimeout);
        }

        return $schema;
    }

    public function getColumnMap(string $tableName): array {
        $map = array();

        $stmt = $this->prepare("SHOW FULL COLUMNS FROM " . $this->quoteTableName($tableName));
        try {

            if ($stmt->execute()) {
                $columns = $stmt->fetchAll();
                foreach ($columns as $info) {
                    $column = $this->loadColumnSchema($info);
                    $map[$column["name"]] = $column;
                }
            }
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage(), (int) $ex->getCode());
        }

        return $map;
    }

    protected function loadColumnSchema(array $info): array {

        static $typeMap = [
            // abstract type => php type
            'smallint' => 'integer',
            'integer' => 'integer',
            'bigint' => 'integer',
            'int' => 'integer',
            'boolean' => 'boolean',
            'float' => 'double',
            'double' => 'double',
            'binary' => 'resource',
        ];

        $column = array();

        $column["name"] = $info['Field'];
        $column["allowNull"] = $info['Null'] === 'YES';
        $column["isPrimaryKey"] = strpos($info['Key'], 'PRI') !== false;
        $column["autoIncrement"] = stripos($info['Extra'], 'auto_increment') !== false;
        $column["comment"] = $info['Comment'];

        $column["dbType"] = $info['Type'];
        $column["unsigned"] = stripos($column["dbType"], 'unsigned') !== false;

        $column["type"] = static::TYPE_STRING;
        if (preg_match('/^(\w+)(?:\(([^\)]+)\))?/', $column["dbType"], $matches)) {
            $type = strtolower($matches[1]);
            if (isset($typeMap[$type])) {
                $column["type"] = $typeMap[$type];
            }

            if (!empty($matches[2])) {
                if ($type === 'enum') {
                    $values = explode(',', $matches[2]);
                    foreach ($values as $i => $value) {
                        $values[$i] = trim($value, "'");
                    }
                    $column["enumValues"] = $values;
                } else {
                    $values = explode(',', $matches[2]);
                    $column["size"] = $column["precision"] = (int) $values[0];
                    if (isset($values[1])) {
                        $column["scale"] = (int) $values[1];
                    }
                    if ($column["size"] === 1 && $type === 'bit') {
                        $column["type"] = 'boolean';
                    } elseif ($type === 'bit') {
                        if ($column["size"] > 32) {
                            $column["type"] = 'bigint';
                        } elseif ($column["size"] === 32) {
                            $column["type"] = 'integer';
                        }
                    }
                }
            }
        }

        $column["phpType"] = $this->getColumnPhpType($column);

        if (!$column["isPrimaryKey"]) {
            if ($column["type"] === 'timestamp' && $info['Default'] === 'CURRENT_TIMESTAMP') {
                $column["defaultValue"] = date("Y-m-d H:i:s");
            } elseif (isset($type) && $type === 'bit') {
                $column["defaultValue"] = bindec(trim($info['Default'], 'b\''));
            } else {
                $column["defaultValue"] = $info['Default'];
            }
        }

        return $column;
    }

    protected function getColumnPhpType(array $column): string {
        static $typeMap = [
            // abstract type => php type
            'smallint' => 'integer',
            'integer' => 'integer',
            'bigint' => 'integer',
            'boolean' => 'boolean',
            'float' => 'double',
            'double' => 'double',
            'binary' => 'resource',
        ];
        if (isset($typeMap[$column["type"]])) {
            if ($column["type"] === 'bigint') {
                return PHP_INT_SIZE === 8 && !$column["unsigned"] ? 'integer' : 'string';
            } elseif ($column["type"] === 'integer') {
                return PHP_INT_SIZE === 4 && $column["unsigned"] ? 'string' : 'integer';
            } else {
                return $typeMap[$column["type"]];
            }
        } else {
            return 'string';
        }
    }

    public function getPDOType(string $type): int {
        $pdoType = PDO::PARAM_STR;
        switch ($type) {
            case "integer":
            case "double":
                $pdoType = PDO::PARAM_INT;
                break;
            case "boolean":
                $pdoType = PDO::PARAM_BOOL;
                break;
            case "resource":
            case "string":
                $pdoType = PDO::PARAM_STR;
                break;
        }
        return $pdoType;
    }

    public function escapeValue(string $value, string $type): string {
        return $this->quote($value, $this->getPDOType($type));
    }

    public function unescape(string $value): string {
        return stripslashes(substr($value, 1, -1));
    }

}
