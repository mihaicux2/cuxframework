<?php

namespace CuxFramework\console;

use CuxFramework\utils\Cux;
use CuxFramework\console\CuxCommand;
use CuxFramework\utils\CuxSlug;

class ExportTranslationsCommand extends CuxCommand{
    
    public $outputFile;
    public $translationsDir = "i18n";
    
    private $_translations;
    
    public function run(array $args) {
    
        $this->parseArguments($args);
        
        if (!$this->outputFile){
            $this->outputFile = "./messages.xlsx";
        }
        
        $this->loadExistingTranslations();
        
        echo $this->getColoredString("Getting source files...", "yellow", "black");
        $files = $this->findFilesRecursive(realpath("./"), array(
            "fileTypes" => array(
                "php"
            ),
            "exclude" => array(
                "assets",
                "config",
                "i18n",
                "log",
                "img",
                "queries",
                "runtime",
                "uploads",
                "vendor"
            )
        ));
        echo $this->getColoredString("DONE!", "green", "yellow").PHP_EOL;
        
        echo $this->getColoredString("Extracting messages...", "yellow", "black");
        $messages = $this->extractAllMessages($files);
        echo $this->getColoredString("DONE!", "green", "yellow").PHP_EOL;
        
        echo $this->getColoredString("Writing output file...", "yellow", "black");
        $writer = Cux::getInstance()->exporter->createWriter($this->outputFile, "xlsx", false);        
        
        $headerRow = array("Category", "Message", "Details");
        foreach (array_keys($this->_translations) as $lang){
            $headerRow[] = "Lang:{$lang}";
        }
        $row = Cux::getInstance()->exporter->createRowFromArray($headerRow, Cux::getInstance()->exporter->getHeaderStyle());
        $writer->addRow($row);
        foreach ($messages as $message){
            $row = Cux::getInstance()->exporter->createRowFromArray($message, Cux::getInstance()->exporter->getBorderedStyle());
            $writer->addRow($row);
        }
        echo $this->getColoredString("DONE!", "green", "yellow").PHP_EOL;
        echo $this->getColoredString("Output file: ".realpath($this->outputFile), "green", "black").PHP_EOL;
        
        $writer->close();
        
    }
    
    private function loadExistingTranslations(){
        if (!file_exists($this->translationsDir) || !is_readable($this->translationsDir)){
            echo $this->getColoredString("Please make sure the translations directory folder exists and is readable", "red", "black");
            return;
        }
        
        $fh = opendir($this->translationsDir);
        if (!$fh) {
            throw new \Exception("Unable to open directory: ".$this->translationsDir);
        }
        
        while(($fName=readdir($fh)) !== false){
            if ($fName == "." || $fName == "..") continue;
            $fPath = $this->translationsDir.DIRECTORY_SEPARATOR.$fName;
             if (is_file($fPath)){
                if (strpos($fName, ".") !== false){
                    $exts = explode(".", $fName);
                    $ext = strtolower(end($exts));
                    $lang = $exts[0];
                    
//                    echo $lang." | ".$ext.PHP_EOL
                    
                    if ($ext == "php"){
                        if (!isset($this->_translations[$lang])) {
                            $this->_translations[$lang] = array();
                        }
                        $this->_translations[$lang] = array_merge($this->_translations[$lang], require_once($fPath));
                    }
                }
             }
        }
        
    }
    
    private function extractAllMessages(array $files = array()){
        $messages = array();
        
        if (!empty($files)){
            foreach ($files as $file){
                $messages = array_merge($messages, $this->extractMessages($file));
            }
        }
        
        return $messages;
    }

    private function extractMessages($fName){
        $messages = array();
        
        $subject = file_get_contents($fName);
        //  Pattern                                   |       category           |            message      |               params                 |  message details   |
        $pattern = '/\bCux::translate\s*\(\s*[\'\"]([^\'\"]+)[\'\"]\s*,\s*[\'\"]([^\'\"]+)[\'\"]\s*(,\s*(array\([^\)]*\)|\[([^\]]+)\]))*(,\s*[\'\"]([^\'\"]+)[\'\"])*/ims';
        $totalFound = preg_match_all($pattern, $subject, $matches, PREG_SET_ORDER);
        if ($totalFound){
//            echo $totalFound;
            for ($i = 0; $i < $totalFound; $i++){
                $match = $matches[$i];
                $category = $match[1];
                $message = $match[2];
                $details = isset($match[7]) ? $match[7] : "";
                $rowData = array(
                    "category" => $category,
                    "message" => $message,
                    "details" => $details
                );
                foreach ($this->_translations as $lang => $translations){
                    $rowData["Lang:{$lang}"] = isset($translations[$category][$message]) ? $translations[$category][$message] : " ";
                }
                $messages[$category.".".$message] = $rowData;
            }
        }
        
        return $messages;
    }
    
    private function findFilesRecursive(string $path, array $options = array()){
        $list = array();
        
        $fh = opendir($path);
        if (!$fh) {
            throw new \Exception("Unable to open directory: ".$dir);
        }
        
        $fileTypes = isset($options["fileTypes"]) ? array_flip($options["fileTypes"]) : array();
        $excludedDirs = isset($options["exclude"]) ? array_flip($options["exclude"]) : array();
        
        while(($fName=readdir($fh)) !== false){
            if ($fName == "." || $fName == "..") continue;
            $fPath = $path.DIRECTORY_SEPARATOR.$fName;
            if (is_file($fPath)){
                if (strpos($fName, ".") !== false){
                    $exts = explode(".", $fName);
                    $ext = strtolower(end($exts));
                    if (!isset($options["fileTypes"]) || isset($fileTypes[$ext])){
                        $list[] = $fPath;
                    }
                }
            } elseif (!isset($options["exclude"]) || !isset($excludedDirs[$fName])) {
                $list = array_merge($list, $this->findFilesRecursive($fPath, $options));
            }
        }
        
        closedir($fh);
        
        return $list;
    }
    
}