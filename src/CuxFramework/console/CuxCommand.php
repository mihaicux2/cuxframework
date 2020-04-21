<?php

namespace CuxFramework\console;

use CuxFramework\utils\CuxBaseObject;

abstract class CuxCommand extends CuxBaseObject{
    
    private $foreground_colors = array();
    private $background_colors = array();
    private $startTime = null;
    
    public function __construct() {
        
        $this->startTime = microtime(true);
        
        // set up shell colors
        $this->foreground_colors['black'] = '0;30';
        $this->foreground_colors['dark_gray'] = '1;30';
        $this->foreground_colors['blue'] = '0;34';
        $this->foreground_colors['light_blue'] = '1;34';
        $this->foreground_colors['green'] = '0;32';
        $this->foreground_colors['light_green'] = '1;32';
        $this->foreground_colors['cyan'] = '0;36';
        $this->foreground_colors['light_cyan'] = '1;36';
        $this->foreground_colors['red'] = '0;31';
        $this->foreground_colors['light_red'] = '1;31';
        $this->foreground_colors['purple'] = '0;35';
        $this->foreground_colors['light_purple'] = '1;35';
        $this->foreground_colors['brown'] = '0;33';
        $this->foreground_colors['yellow'] = '1;33';
        $this->foreground_colors['light_gray'] = '0;37';
        $this->foreground_colors['white'] = '1;37';

        $this->background_colors['black'] = '40';
        $this->background_colors['red'] = '41';
        $this->background_colors['green'] = '42';
        $this->background_colors['yellow'] = '43';
        $this->background_colors['blue'] = '44';
        $this->background_colors['magenta'] = '45';
        $this->background_colors['cyan'] = '46';
        $this->background_colors['light_gray'] = '47';

        // register shutdown behaviour
        if (php_sapi_name() == "cli") {
            // In cli-mode
            $this->registerShutdownFunctions();
            $this->clrScr();
        }
        
        $fullClassName = get_called_class();
        $names = explode("\\", $fullClassName);
        $name = lcfirst(substr(end($names), 0, -7));
        echo $this->getColoredString("Executing command {$name} ...", "yellow", "black").PHP_EOL.PHP_EOL;
    }
    
    protected function parseArguments($args) {
        parse_str(implode('&', $args), $args);
        foreach ($args as $name => $value) {
            if (property_exists($this, $name))
                $this->$name = $value;
        }
    }
    
    // remember to call this method if the command is called from the terminal(/console)
    public function registerShutdownFunctions() {

        register_shutdown_function(array($this, "scriptEnd"));

        // attach process terminate/interrupt behaviour
        declare(ticks = 1);
        pcntl_signal(SIGINT, array($this, 'scriptKillSignal'));
        pcntl_signal(SIGTERM, array($this, 'scriptKillSignal'));
        pcntl_signal(SIGHUP, array($this, 'scriptKillSignal'));
    }

    public function getScriptStats() {

        $endTime = microtime(true);
        $secs = $endTime - $this->startTime;
        
        $hours = floor($secs / 3600);
        $secs -= $hours * 3600;
        $mins  = floor($secs / 60);
        $secs -= $mins * 60;
        $secs  = round($secs, 2);

        return array(
            "duration" => array(
                "hours" => $hours,
                "minutes" => $mins,
                "seconds" => $secs
            ),
            "params" => $this->params,
            "startTime" => date("Y-m-d, H:i:s", $this->startTime),
            "endTime" => date("Y-m-d, H:i:s"),
            "peakMemory" => $this->convert(memory_get_peak_usage())
        );
    }

    public function scriptEnd() {
        if (!$this->fromInterface) {
            $stats = $this->getScriptStats();
            echo PHP_EOL;

            echo $this->getColoredString("Ended at : " . $stats["endTime"], "green") . "\n";
            echo $this->getColoredString("Finished execution in " . $stats["duration"]["hours"] . " hour(s), " . $stats["duration"]["minutes"] . " minute(s)" . " and " . $stats["duration"]["seconds"] . " second(s)", "light_blue", "black") . "\n";
            echo $this->getColoredString("Maximum memory used: " . $stats["peakMemory"], "light_blue", "black") . "\n\n";
        }
    }
    
    protected function clrScr(){
        echo chr(27).chr(91).'H'.chr(27).chr(91).'J';   //^[H^[J  
    }
    
    public function scriptKillSignal($sig) {
        if (!$this->fromInterface) {
            switch ($sig) {
                case SIGINT:
                    echo "\n" . $this->getColoredString("Received signal: SIGINT. Process interrupted", "purple", "yellow") . "\n";
                    break;
                case SIGTERM:
                    echo "\n" . $this->getColoredString("Received signal: SIGTERM. Process terminated", "purple", "yellow") . "\n";
                    break;
                case SIGHUP:
                    echo "\n" . $this->getColoredString("Received signal: SIGHUP. Process suspended", "purple", "yellow") . "\n";
                    break;
            }
        }
        exit(); // this will call the registered shut down function (if any is defined)
    }

    public function usleep($microseconds) {
        if (!is_int($microseconds) || $microseconds < 0) {
            if (!$this->fromInterface) {
                echo "\n" . $this->getColoredString("The input parameter '\$microseconds' must be a positive integer", "yellow") . "\n";
            }
            return;
        }
        if (!$this->fromInterface) {
            echo "\n" . $this->getColoredString("Sleeping for $microseconds microseconds", "yellow") . "\n";
        }
        usleep($microseconds);
        clearstatcache();
        gc_collect_cycles();
    }

    public function sleep($seconds) {
        if (!is_int($seconds) || $seconds < 0) {
            if (!$this->fromInterface) {
                echo "\n" . $this->getColoredString("The input parameter '\$seconds' must be a positive integer", "yellow") . "\n";
            }
            return;
        }
        if (!$this->fromInterface) {
            echo "\n" . $this->getColoredString("Sleeping for $seconds seconds", "yellow") . "\n";
        }
        sleep($seconds);
        clearstatcache();
        gc_collect_cycles();
    }

    // Returns colored string
    public function getColoredString($string, $foreground_color = null, $background_color = null) {
        $colored_string = "";

        // Check if given foreground color found
        if (isset($this->foreground_colors[$foreground_color])) {
            $colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
        }
        // Check if given background color found
        if (isset($this->background_colors[$background_color])) {
            $colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
        }

        // Add string and end coloring
        $colored_string .= $string . "\033[0m";

        return $colored_string;
    }

    // Returns all foreground color names
    public function getForegroundColors() {
        return array_keys($this->foreground_colors);
    }

    // Returns all background color names
    public function getBackgroundColors() {
        return array_keys($this->background_colors);
    }

    public function printMessage($message){
        echo "[ ".date("Y-m-d H:i:s")." ]: ".$message.PHP_EOL;
    }
    
    protected static final function delete(&$var) {
        $var = null;
        unset($var);
        gc_collect_cycles();
    }

    protected final function intOnly($var) {
        return (int) $var > 0;
    }

    protected final function ipOnly($var) {
        return preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $var);
    }

    protected function normalizeName($name) {

        $name = preg_replace('~[^\\pL\d]+~u', '-', $name);

        // trim
        $name = trim($name, '-');

        // transliterate
        $name = iconv('utf-8', 'us-ascii//TRANSLIT', $name);

        // lowercase
        $name = strtolower($name);

        // remove unwanted characters
        $name = preg_replace('~[^-\w]+~', '', $name);

        if (empty($name)) {
            return 'n-a';
        }

        return $name;
    }

    protected function getDate($datetime){
        return trim($datetime) ? date("Y-m-d", strtotime($datetime)) : " ";
    }
    
    protected function getTime($datetime){
        return trim($datetime) ? date("H:i:s", strtotime($datetime)) : " ";
    }
    
    protected function cleanValue(&$str) {
//        $str = preg_replace('~[^\\pL\d\ ]+~u', '-', $str);
//        $str = trim($str, '-');
        $str = iconv('utf-8', 'us-ascii//TRANSLIT', $str);
//        $str = preg_replace('~[^-\w]+~', '', $str);
        if (empty($str))
            $str = " ";
    }
    
    protected function convert($size) {
        if (!$size) return "0b";
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return sprintf("%.2f %s", $size / pow(1024, ($i = floor(log($size, 1024)))), ' ' . $unit[$i]);
    }
    
    protected function getSwapMemoryUsage(){
        if (PHP_OS == "Linux"){
            $systemStatus = trim(str_replace("VmSwap: ", "", preg_replace('!\s+!', ' ', shell_exec("grep VmSwap /proc/".$this->pID."/status"))));
            return $systemStatus;
        }
        return "can't do";
    }
    
    protected function swap(&$a, &$b){
        $x = $a;
        $a = $b;
        $b = $x;
        $x = null;
        unset($x);
    }
    
    abstract public function run(array $args);
    
    

}