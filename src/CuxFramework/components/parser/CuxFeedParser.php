<?php

namespace CuxFramework\components\parser;

use CuxFramework\utils\CuxBaseObject;

class CuxFeedParser extends CuxBaseObject {
    
    private $_posts = array();
    
    public function config(array $config) {
        parent::config($config);
    }
    
    function parse(string $urlOrPath): bool {
//        $content = file_get_contents($urlOrPath);
//        $content = preg_replace('#(^\s*<!\[CDATA\[|\]\]>\s*$)#sim', '', (string)$content);
//        echo $content;
//        die();
//        if (!($dom = simplexml_load_string($content, "SimpleXMLElement", LIBXML_NOERROR | LIBXML_ERR_NONE))){
//        if (!($dom = simplexml_load_string($content, "SimpleXMLElement", LIBXML_NOCDATA))){
//        if (!($dom = simplexml_load_string($content))){
//            return;
//        }
        
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
    
    public function getPosts(){
        return $this->_posts;
    }
}

