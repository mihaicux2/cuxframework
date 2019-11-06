<?php

namespace CuxFramework\models;

use CuxFramework\utils\Cux;
use CuxFramework\components\db\CuxDBObject;
use CuxFramework\models\CuxEntity;

class CuxDbUser extends CuxEntity {
    
    public static function tableName(): string{
        return "cux_user";
    }
    
    public function relations(): array {
        return array();
    }
    
    public function getRoleName(){
        return \components\user\CuxUser::$roleNames[$this->user_role_id_fk];
    }
    
    public function delete(): bool {
        return true;
    }
    
}

