<?php



namespace models;

use CuxFramework\utils\Cux;
use CuxFramework\components\db\CuxDBObject;

class CuxDBModels extends CuxDBObject{
    
    public static function tableName(): string {
        return "faculty";
    }
    
    public function getPk(): array {
        return parent::getPk();
    }
    
    public function rules() {
        return parent::rules();
    }
    
    public function labels() {
        parent::labels();
    }
    
    public function relations(): array {
        return parent::relations();
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