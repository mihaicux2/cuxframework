<?php

namespace CuxFramework\console;

use CuxFramework\utils\Cux;
use CuxFramework\console\CuxCommand;
use CuxFramework\utils\CuxSlug;


use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\CSV\Reader as CSVReader;

class ImportTranslationsCommand extends CuxCommand{
    
    public $inputFile;
    public $outputDir = "i18n";
    public $delimiter = ",";
    public $backupFirst = true;
    
    public function run(array $args) {
        
        $this->parseArguments($args);
        
        if (!$this->inputFile){
            echo $this->getColoredString("Please provide an input file", "red", "black");
            return;
        }
        
        if (!file_exists($this->inputFile) || !is_readable($this->inputFile)){
            echo $this->getColoredString("Please make sure the input file exists and is readable", "red", "black");
            return;
        }
        try {
            $reader =  ReaderEntityFactory::createReaderFromFile($this->inputFile);
            $reader->open($this->inputFile);
            if ($reader instanceof CSVReader){
                $reader->setDelimiter($this->delimiter);
            }
            
            $messages = array();
            $langs = array();
            
            foreach ($reader->getSheetIterator() as $sheet) {
                $it = 0;
                foreach ($sheet->getRowIterator() as $row) {
                    $cells = $row->getCells();
                    if ($it == 0){
                        foreach ($cells as $it => $cell){
                            $value = $cell->getValue();
                            if (preg_match("/\blang\:([a-z\-\_]+)/is", $value, $matches)){
                                $lang = strtolower($matches[1]);
                                $langs[$lang] = $it;
                                $messages[$lang] = array();
                            }
                        }
                    } else {
                        $category = $cells[0]->getValue();
                        $message = $cells[1]->getValue();
                        foreach ($langs as $language => $column){
                            if (!isset($messages[$language][$category])){
                                $messages[$language][$category] = array();
                            }
                            $messages[$language][$category][$message] = $cells[$column]->getValue();
                        }
                    }
                    $it++;
                }
                break; // we are only intereset in the first sheet
            }
            
            $reader->close();
            
            foreach ($messages as $language => $localeMessages){
                $fName = $this->outputDir.DIRECTORY_SEPARATOR.$language.".php";
                if ($this->backupFirst){
                    @rename($fName, $fName.".bak");
                }
                file_put_contents($fName, '<?php return '.var_export($localeMessages, true).';');
            }
            
        } catch (\Exception $e){
            print_r($e);
            echo $this->getColoredString("Please,provide a file that is readable by box/spout", "red", "black");
            return;
        }
        
//        echo $this->getColoredString("Getting files...", "yellow", "black");
//        $files = $this->findFilesRecursive(realpath("./"), array(
//            "fileTypes" => array(
//                "php"
//            ),
//            "exclude" => array(
//                "assets",
//                "config",
//                "i18n",
//                "log",
//                "img",
//                "queries",
//                "runtime",
//                "uploads",
//                "vendor"
//            )
//        ));
//        echo $this->getColoredString("DONE!", "green", "yellow").PHP_EOL;
//        
//        echo $this->getColoredString("Extracting messages...", "yellow", "black");
//        $messages = $this->extractAllMessages($files);
//        echo $this->getColoredString("DONE!", "green", "yellow").PHP_EOL;
//        
//        echo $this->getColoredString("Writing output file...", "yellow", "black");
//        $filePath = "./messages.xlsx";
//        $writer = Cux::getInstance()->exporter->createWriter($filePath, "xlsx", false);        
//        
//        $row = Cux::getInstance()->exporter->createRowFromArray(array("Category", "Message", "Details"), Cux::getInstance()->exporter->getHeaderStyle());
//        $writer->addRow($row);
//        foreach ($messages as $message){
//            $row = Cux::getInstance()->exporter->createRowFromArray($message, Cux::getInstance()->exporter->getBorderedStyle());
//            $writer->addRow($row);
//        }
//        echo $this->getColoredString("DONE!", "green", "yellow").PHP_EOL;
//        echo $this->getColoredString("Output file: ".realpath($filePath), "green", "black").PHP_EOL;
//        
//        $writer->close();
        
        
        
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
                $messages[] = array(
                    "category" => $match[1],
                    "message" => $match[2],
                    "details" => isset($match[7]) ? $match[7] : ""
                );
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
    
    public function help(): string{
         $str = "";
        
        $str .= $this->getColoredString("                  ImportTranslations Command                    ", "light_green", "black").PHP_EOL.PHP_EOL;
        $str .= $this->getColoredString("    This command is used to parse the translations file (XLSX) and generate the translations file(s) ", "blue", "yellow").PHP_EOL;
        $str .= "Mandatory parameters: ".PHP_EOL;
        $str .= "\tinputFile - String. The location and name for the XLSX file to be parsed".PHP_EOL;
        $str .= "Optional parameters: ".PHP_EOL;
        $str .= "\toutputDir - String, defaults to 'i18n'. The location for the existing translations".PHP_EOL;
        $str .= "\tbackupFirst - Bool, defaults to 1. Make back-up copies for the existing translation files".PHP_EOL.PHP_EOL;
        $str .= "Usage example: ./maintenance importTranslations inputFile=messages2.xlsx".PHP_EOL;
        
        return $str;
    }
    
}