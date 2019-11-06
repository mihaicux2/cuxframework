<?php

namespace CuxFramework\models;

use components\db\CuxDBObject;

class TrafficLog extends CuxDBObject {
    
    public static function tableName(): string {
        return "cux_traffic_log";
    }
    
    public function relations(): array {
        return array(
            "user" => array(
                "type" => static::BELONGS_TO,
                "class" => User::className(),
                "key" => array(
                    "user_id_fk"
                )
            )
        );
    }
    
}

