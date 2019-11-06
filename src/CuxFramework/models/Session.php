<?php

namespace CuxFramework\models;

use components\db\CuxDBObject;

class Session extends CuxDBObject {
    
    public static function tableName(): string {
        return "cux_user_session";
    }

    public function relations(): array {
        return array(
            "user" => array(
                "type" => static::BELONGS_TO,
                "class" => CuxDbUser::className(),
                "key" => array(
                    "user_id"
                )
            )
        );
    }
    
}

