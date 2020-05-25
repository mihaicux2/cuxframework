<?php

namespace {namespace};

use CuxFramework\utils\Cux;
use CuxFramework\components\db\CuxDBObject;

/**
 * Model class for {modelName}
 * 
 * The following properties are available
 *  {properties}
*/
class {modelName} extends {baseModel}{
    
    public static function tableName(): string {
        return "{tableName}";
    }
    
    public function getPkName(): array {
        // return parent::getPkName();
        return {pk};
    }
    
    public function rules() {
        return {rules};
    }
    
    public function labels() {
        return {labels};
    }
    
    public function relations(): array {
        return {relations};
    }
    
    public function beforeInsert($fields = array()): bool {
        return parent::beforeInsert($fields);
    }
    
    public function beforeUpdate($fields = array()): boolean {
        return parent::beforeUpdate($fields);
    }
    
    public function beforeDelete(): bool {
        return parent::beforeDelete();
    }
    
    public function afterInsert($fields = array()) {
        parent::afterInsert($fields);
    }
    
    public function afterUpdate($fields = array()) {
        parent::afterUpdate($fields);
    }
    
    public function afterDelete() {
        parent::afterDelete();
    }
    
}

?>