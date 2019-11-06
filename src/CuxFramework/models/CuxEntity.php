<?php

namespace CuxFramework\models;

use CuxFramework\components\db\CuxDBObject;
use CuxFramework\utils\Cux;

abstract class CuxEntity extends CuxDBObject {
    
    static $isDeleting = false;
    
    public function beforeInsert($fields=array()): bool {
        
        $this->created_at = date("Y-m-d H:i:s");
        if (!Cux::getInstance()->user->isGuest()){
            $this->created_by = Cux::getInstance()->user->getId();
        }
        
        return true;
    }
    
    public function beforeUpdate($fields=array()): bool {
        
        if (!empty($fields)){
            $fields = array_flip($fields);
        }
        
        if (!static::$isDeleting){
            if (empty($fields) || isset($fields["updated_at"])){
                $this->updated_at = date("Y-m-d H:i:s");
                if (!Cux::getInstance()->user->isGuest()){
                    $this->updated_by = Cux::getInstance()->user->getId();
                }
            }
        }
        else{
            $this->deleted_at = date("Y-m-d H:i:s");
            if (!Cux::getInstance()->user->isGuest()){
                $this->deleted_by = Cux::getInstance()->user->getId();
            }
        }
        
        return true;
    }
    
    public function beforeDelete(): bool {
        
        static::$isDeleting = true;
        $this->update(array("deleted_by", "deleted_at"));
        static::$isDeleting = false;
        return true;
    }

    public function getLastUpdated($full=false): string{
        if ($this->updated_at){
            return Cux::getInstance()->timeEllapsed($this->updated_at, $full);
        }
        return Cux::getInstance()->timeEllapsed($this->created_at, $full);
    }
    
}