<?php

/**
 * CuxTraffic abstract class file
 * 
 * @package Components
 * @subpackage Traffic
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\traffic;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\Cux;

/**
 * Abstract class that serves as a starting point for storing all the website requests
 */
abstract class CuxTraffic extends CuxBaseObject {
    
    /**
     * The list of IP's that are not monitored
     * @var array
     */
    public $ignoredIPs = array();
    
    /**
     * If set to true, ignore (do not store/process) AJAX requests
     * @var bool
     */
    public $ignoreAjax = true;
    
    /**
     * Setup object instance properties
     * @param array $config
     */
    public function config(array $config) {
        parent::config($config);
    }
 
    /**
     * Get details about the current website visitor: user id, request date, user IP, user country, request body, etc.
     * @return array
     */
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
    
    /**
     * Check if the current request should be ignored ( not processed/stored)
     * @return bool
     */
    public function ignoreRequest(){
        return ((Cux::getInstance()->request->isAjax() && $this->ignoreAjax) || in_array(Cux::getInstance()->request->getIp(), $this->ignoredIPs));
    }
    
    /**
     * Abstract method that should be implemented by the extending classes
     * Process/store current request details
     */
    abstract public function logRequest();
    
}