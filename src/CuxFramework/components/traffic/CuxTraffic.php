<?php

namespace CuxFramework\components\traffic;

use CuxFramework\utils\CuxSingleton;
use CuxFramework\utils\CuxBase;
use CuxFramework\utils\Cux;

abstract class CuxTraffic extends CuxSingleton {
    
    /**
     * The list of IP's that are not monitored
     * @var array
     */
    public $ignoredIPs = array();
    
    public $ignoreAjax = true;
    
    public static function config(array $config): void {
        $ref = static::getInstance();
        CuxBase::config($ref, $config);
    }
 
    public function getVisitorsInfo(){
        $visitorInfo = Cux::getInstance()->request->getVisitorInfo();
        if (is_null($visitorInfo)) {
            $visitorInfo = array(
                "continent_code" => "",
                "country_code" => "",
                "country_code3" => "",
                "country_name" => "",
                "region" => "",
                "city" => "",
                "postal_code" => "",
                "latitude" => "",
                "longitude" => "",
                "dma_code" => "",
                "area_code" => "",
            );
        }

        return array(
            "user_id_fk" => Cux::getInstance()->user->getId(),
            "request_date" => date("Y-m-d H:i:s"),
            "request_ip" => Cux::getInstance()->request->getIp(),
            "request_uri" => Cux::getInstance()->request->getUri(),
            "request_method" => Cux::getInstance()->request->getMethod(),
            "request_get_data" => Cux::getInstance()->request->getParams(),
            "request_post_data" => Cux::getInstance()->request->getPosts(),
            "request_browser" => Cux::getInstance()->request->getUserAgent(),
            "request_referer" => Cux::getInstance()->request->getReferer(),
            "request_status" => http_response_code(),
            "request_continent_code" => $visitorInfo["continent_code"],
            "request_country_code" => $visitorInfo["country_code"],
            "request_country_code3" => $visitorInfo["country_code3"],
            "request_country_name" => $visitorInfo["country_name"],
            "request_region" => $visitorInfo["region"],
            "request_city" => $visitorInfo["city"],
            "request_postal_code" => $visitorInfo["postal_code"],
            "request_latitude" => $visitorInfo["latitude"],
            "request_longitude" => $visitorInfo["longitude"],
            "request_dma_code" => $visitorInfo["dma_code"],
            "request_area_code" => $visitorInfo["area_code"],
        );
    }
    
    public function ignoreRequest(){
        return ((Cux::getInstance()->request->isAjax() && $this->ignoreAjax) || in_array(Cux::getInstance()->request->getIp(), $this->ignoredIPs));
    }
    
    abstract public function logRequest();
    
}