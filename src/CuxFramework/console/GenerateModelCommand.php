<?php

namespace CuxFramework\console;

use CuxFramework\utils\Cux;
use CuxFramework\console\CuxCommand;
use CuxFramework\utils\CuxSlug;

class GenerateModelCommand extends CuxCommand{
    
    public $modelName;
    public $tableName;
    public $withRelations = true;
    public $translate = true;
    public $translationCategory = "entities";
    public $template = "";
    public $baseModel = "CuxDBObject";
    public $outputDir = "models";
    
    public function run(array $args) {
        
        $this->parseArguments($args);
        
        if (!$this->tableName){
            echo $this->getColoredString("You must privide at least the tableName parameter", "red", "black").PHP_EOL;
            return;
        }
        
        if (!$this->template){
            $frameworkPath = "vendor".DIRECTORY_SEPARATOR."mihaicux".DIRECTORY_SEPARATOR."cuxframework".DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."CuxFramework".DIRECTORY_SEPARATOR;
            $this->template = $frameworkPath."CuxDBModel.tpl";
        }
        
        if (!file_exists($this->template) || !is_readable($this->template)){
            echo $this->getColoredString("Make sure the template file ({$this->template}) exists and is readable", "red", "black").PHP_EOL;
            return;
        }
        
        if (!is_dir($this->outputDir) or !is_writable($this->outputDir)) {
            echo $this->getColoredString("Please make sure the output directory exists and is writable: {$this->outputDir}", "red", "black").PHP_EOL;
            return;
        }
        
        if (!$this->modelName){
            $this->modelName = ucfirst(CuxSlug::camelCase($this->tableName));
        }
        
        echo $this->getColoredString("Generating model for table `{$this->tableName}`: {$this->modelName}...", "light_green", "black").PHP_EOL;
        
        $namespace = str_replace(DIRECTORY_SEPARATOR, "\\", $this->outputDir);
        
        $outputFile = $this->outputDir.DIRECTORY_SEPARATOR.$this->modelName.".php";
        
        if (is_file($outputFile) and !is_writable($outputFile)) {
            $this->getColoredString("The output file exists and is not writable: {$outputFile}", "red", "black").PHP_EOL;
            return;
        }
        
        $schema = $this->getTableSchema();
        
        $rules = array(
            "notEmpty" => array(),
            "length" => array(),
            "numeric" => array()
        );
        $labels = array();
        
        $props = array();
        
        foreach ($schema["columns"] as $column => $properties){
            $columnTitle = $this->getColumnNameLabel($column);
            $labels[$column] = "\"{$column}\" => ". ($this->translate ? "Cux::translate(\"".$this->translationCategory."\", \"".$columnTitle."\")" : ("\"{$columnTitle}\""));
            if (!$properties["allowNull"]){
                $rules["notEmpty"][$column] = $column;
            }
            if ($properties["phpType"] == "integer" || $properties["phpType"] == "double"){
                $rules["numeric"][$column] = $column;
            }
            if ($properties["phpType"] == "string" && isset($properties["size"])){
                $rules["length"][$properties["size"]][$column] = $column;
            }
            $props[] = $properties["phpType"]." $".$column;
        }     
        
        $rulesArr = array();
        
        if (!empty($rules["notEmpty"])){
            $rulesArr[] = "array(
                \"validator\" => \"CuxFramework\\\\components\\\\validator\\\\CuxNotEmptyValidator\",
                \"fields\" => array(
                     \"".implode("\",\n\t    \"", $rules["notEmpty"])."\"
                )
            )";
        }
        if (!empty($rules["numeric"])){
            $rulesArr[] = "array(
                \"validator\" => \"CuxFramework\\\\components\\\\validator\\\\CuxIsNumericValidator\",
                \"params\" => array(
                    \"allowEmpty\" => true,
                ),
                \"fields\" => array(
                     \"".implode("\",\n\t    \"", $rules["numeric"])."\"
                )
            )";
        }
        if (!empty($rules["length"])){
            foreach ($rules["length"] as $maxLength => $fields){
                $rulesArr[] = "array(
                \"validator\" => \"CuxFramework\\\\components\\\\validator\\\\CuxLengthValidator\",
                \"params\" => array(
                    \"maxLength\" => {$maxLength}
                ),
                \"fields\" => array(
                    \"".implode("\",\n\t    \"", $fields)."\"
                )
            )";
            }            
        }
        
        $rulesStr = empty($rulesArr) ? "parent::rules()" : "array(
            ".implode(",\n\t    ", $rulesArr)."
        )";
        
        $relationsStr = "parent::relations()";
        
        if ($this->withRelations){
            $rows = $this->getTableRelations();
            $relationsArr = array();
            if (!empty($rows)){
                $props[] = "";
                
                foreach ($rows as $row){
                    
                    if ($row["TABLE_NAME"] == $this->tableName){ // HAS_ONE | BELONGS_TO
                        
                        $relationName = CuxSlug::camelCase($row["REFERENCED_TABLE_NAME"]);
                        $relatedModel = ucfirst($relationName);
                        
                        $key = $relationName;
                        while (isset($relationsArr[$key])){
                            $key = $key."New";
                        }
                        
                        $props[] = "{$relatedModel} \${$key}";
                        
                        $relationsArr[$key] = "\"{$key}\" => array(
                \"type\" => static::BELONGS_TO,
                \"class\" => {$relatedModel}::className(),
                \"key\" => array(
                    \"{$row["COLUMN_NAME"]}\"
                )
            )";
                    } elseif ($row["REFERENCED_TABLE_NAME"] == $this->tableName){ // HAS_MANY
                        $relationName = CuxSlug::camelCase($row["TABLE_NAME"]);
                        $relatedModel = ucfirst($relationName);
                        
                        $key = $relationName;
                        while (isset($relationsArr[$key])){
                            $key = $key."New";
                        }
                        
                        $props[] = "{$relatedModel}[] \${$key}";
                        
                        $relationsArr[$key] = "\"{$key}s\" => array(
                \"type\" => static::HAS_MANY,
                \"class\" => {$relatedModel}::className(),
                \"key\" => array(
                    \"{$row["COLUMN_NAME"]}\"
                )
            )";
                    }
                }
                
            }
            
            
            if (!empty($relationsArr)){
                $relationsStr = "array(\n\t    ".implode(",\n\t    ", $relationsArr)."\n\t)";
//                foreach *#
            }
        }
        
        $output = file_get_contents($this->template);
        $output = str_replace("{namespace}", $namespace, $output);
        $output = str_replace("{tableName}", $this->tableName, $output);
        $output = str_replace("{properties}", implode("\n *  ", $props), $output);
        $output = str_replace("{modelName}", $this->modelName, $output);
        $output = str_replace("{baseModel}", $this->baseModel, $output);
        $output = str_replace("{pk}", "array(\"".implode("\", \"", $schema["primaryKey"])."\")", $output);
        $output = str_replace("{labels}", "array(\n\t    ".implode(",\n\t    ", $labels)."\n\t)", $output);
        $output = str_replace("{rules}", $rulesStr, $output);
        $output = str_replace("{relations}", $relationsStr, $output);
        
//        $imageDir = "/path/to/images/dir/";
//        $imagePath = "$imageDir$pk.jpg";
        if (!is_dir($this->outputDir) or !is_writable($this->outputDir)) {
            $this->getColoredString("Please make sure the output directory exists and is writable: {$this->outputDir}", "red", "black").PHP_EOL;
        } elseif (is_file($this->outputDir) and !is_writable($this->outputDir)) {
            // Error if the file exists and isn't writable.
        }
        
        file_put_contents($outputFile, $output);
        
        echo $this->getColoredString("DONE generating file: {$outputFile}.", "light_green", "black").PHP_EOL;
//        echo "DONE".PHP_EOL;
        
    }
    
    protected function getColumnNameLabel($column){
        $camelCase = CuxSlug::camelCase($column);
        preg_match_all('/((?:^|[A-Z])[a-z]+)/', $camelCase, $matches);
        return ucfirst(implode(" ", $matches[0]));
    }
    
    protected function getTableRelations(){        
        $sql = "SELECT
                        TABLE_NAME,
                        COLUMN_NAME,
                        CONSTRAINT_NAME,
                        REFERENCED_TABLE_NAME,
                        REFERENCED_COLUMN_NAME
                    FROM
                        INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                    WHERE
                            REFERENCED_TABLE_SCHEMA = :dbName
                    AND (REFERENCED_TABLE_NAME = :tableName OR TABLE_NAME = :tableName)";
        
        $stmt = Cux::getInstance()->db->prepare($sql);
        $stmt->bindValue(":dbName", Cux::getInstance()->db->getDBName());
        $stmt->bindValue(":tableName", $this->tableName);
        
        if ($stmt->execute()) {
            $rows = $stmt->fetchAll();
            
            if ($rows && is_array($rows) && !empty($rows)){
                return $rows;
            }
        }
        
        return array();
    }
    
    protected function getTableSchema() {
        return Cux::getInstance()->db->getTableSchema($this->tableName);
    }

    protected function getColumnMap() {
        return Cux::getInstance()->db->getColumnMap($this->tableName);
    }

    protected function quoteValue($name) {
        return strpos($name, "'") !== false ? $name : "'" . $name . "'";
    }
    
    public function help(): string{
         $str = "";
        
        $str .= $this->getColoredString("                  GenerateModel Command                    ", "light_green", "black").PHP_EOL.PHP_EOL;
        $str .= $this->getColoredString("    This command is used to generate ActiveRecord models for existing database tables ", "blue", "yellow").PHP_EOL;
        $str .= "Mandatory parameters: ".PHP_EOL;
        $str .= "\ttableName - String. The name of the database table to be mapped";
        $str .= "Optional parameters: ".PHP_EOL;
        $str .= "\tmodelName - String. The name of the generated model. By default, it's generated using the camelCase transformation for the given table name".PHP_EOL;
        $str .= "\twithRelations - Bool, defaults to 1. This will also generate any existing database relations for the given table name".PHP_EOL;
        $str .= "\ttranslate - Bool, defaults to 1. Attribute labels will be generated using the `Cux::translate()` method".PHP_EOL.PHP_EOL;
        $str .= "\ttranslationCategory - String, defaults to 'entities'. The translation category for the attribute labels. This has effect only if the `translate` parameter is set to 1".PHP_EOL.PHP_EOL;
        $str .= "\ttemplate - String, defaults to 'CuxDBModel.tpl'. The template file for the generated file".PHP_EOL.PHP_EOL;
        $str .= "\tbaseModel - String, defaults to 'CuxDBObject'. The base ActiveRecord class that the model will inherit".PHP_EOL.PHP_EOL;
        $str .= "\toutputDir - String, defaults to 'models'. The location for the generated file".PHP_EOL.PHP_EOL;
        $str .= "Usage example: ./maintenance generateModel tableName=app_log outputDir=models".PHP_EOL;
        
        return $str;
    }

}