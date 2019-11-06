<?php

namespace CuxFramework\models;

use CuxFramework\components\db\CuxDBObject;

class WebTraffic extends CuxDBObject {
    
    public static function tableName(): string {
        return "cux_traffic_log";
    }

    public function relations(): array {
        return array(
            "contact" => array(
                "type" => static::BELONGS_TO,
                "class" => User::className(),
                "key" => array(
                    "user_id_fk"
                )
            )            
        );
    }
    
}

