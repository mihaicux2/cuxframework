<?php

/**
 * CuxFeedParser class file
 * 
 * @package Components
 * @subpackage Parser
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\parser;

use CuxFramework\utils\CuxBaseObject;

/**
 * Simple class that can be used to parse and process RSS feeds
 */
class CuxFeedParser extends CuxBaseObject {
    
    /**
     * A list of items/posts from a given URL/Path
     * @var array
     */
    private $_posts = array();
    
    /**
     * Setup the object instance properties
     * @param array $config
     */
    public function config(array $config) {
        parent::config($config);
    }
    
    /**
     * Try to parse and process a given RSS feed
     * @param string $urlOrPath The URL or file path for the feed
     * @return bool
     */
    function parse(string $urlOrPath): bool {
        
        if (!($dom = simplexml_load_file($urlOrPath, "SimpleXMLElement", LIBXML_NOCDATA))){
            return false;
        }
        
        foreach ($dom->channel->item as $item){
            $post = array();
            if ($item->title){
                $post["title"] = (string)$item["title"];
            }
            if ($item->description){
                $post["description"] = (string)$item->description;
            }
            if ($item->pubDate){
                $post["date"] = (string)$item->pubDate;
                $post["ts"] = strtotime($post["date"]);
            }
            if ($item->link){
                $post["link"] = (string)$item->link;
            }
            if ($item->author){
                $post["author"] = (string)$item->author;
            }
            if ($item->category){
                $post["category"] = (string)$item->category;
            }
            if ($item->media){
                $post["image"] = (string)$item->media["url"][0];
            }
            if ($item->enclosure){
                $post["image"] = (string)$item->enclosure["url"][0];
            }
            $this->_posts[] = $post;
        }
      
        return count($this->_posts) > 0;
        
    }
    
    /**
     * Getter for the $_posts property
     * @return array
     */
    public function getPosts(){
        return $this->_posts;
    }
}

