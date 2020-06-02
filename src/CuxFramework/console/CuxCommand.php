<?php

/**
 * CuxCommand abstract class file
 */

namespace CuxFramework\console;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\Cux;

/**
 * Abstract class to be used as a starting point for Console Commands
 */
abstract class CuxCommand extends CuxBaseObject{
    
    /**
     * A list of bash codes that can be used to output RGB background for texts in the terminal
     * @var array
     */
    private $foreground_colors = array();
    
    /**
     * A list of bash codes that can be used to output RGB texts in the terminal
     * @var array
     */
    private $background_colors = array();
    
    /**
     * The Console Command start time, in microseconds
     * @var int
     */
    private $startTime = null;
    
    /**
     * Class constructor. 
     */
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
        if (Cux::getInstance()->isConsoleApp()) {
            // In cli-mode
            $this->registerShutdownFunctions();
            $this->clrScr();
        }
        
        $fullClassName = get_class($this);
        $names = explode("\\", $fullClassName);
        $name = lcfirst(substr(end($names), 0, -7));
        
//        if (Cux::getInstance()->isConsoleApp()){
//            echo $this->getColoredString(Cux::translate("core.commands", "Executing command {name}...", array("{name}" => $name), "Message shown while executing console commands"), "light_cyan", "black").PHP_EOL.PHP_EOL;
//        }
    }
    
    /**
     * Parse bash arguments and setup the Command parameters
     * @param array $args
     */
    protected function parseArguments(array $args = array()) {
        
        if (!empty($args)){
            foreach ($args as $arg) {
                if ($arg == "help"){
                    echo $this->help();
                    exit();
                }
            }
        }
        
        parse_str(implode('&', $args), $args);
        foreach ($args as $name => $value) {
            if (property_exists($this, $name))
                $this->$name = $value;
        }
    }
    
    /**
     * PHP script shut down handler. 
     * Handles  script end and also process interruptions
     */
    public function registerShutdownFunctions() {

        register_shutdown_function(array($this, "scriptEnd"));

        // attach process terminate/interrupt behaviour
        declare(ticks = 1);
        pcntl_signal(SIGINT, array($this, 'scriptKillSignal'));
        pcntl_signal(SIGTERM, array($this, 'scriptKillSignal'));
        pcntl_signal(SIGHUP, array($this, 'scriptKillSignal'));
    }

    /**
     * Returns the Command execution status ( execution time, arguments & memory usage)
     * @return array
     */
    public function getScriptStats(): array {

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

    /**
     * Method called on execution end
     */
    public function scriptEnd() {
        if (Cux::getInstance()->isConsoleApp()) {
            $stats = $this->getScriptStats();
            echo PHP_EOL;
            
            $ellapsedTime = array();
            if ($stats["duration"]["hours"]){
                echo "a";
                $ellapsedTime[] = $stats["duration"]["hours"]." ".Cux::translate("core.debug", "hours", array(), "Core message, used for writing execution time");
            }
            if ($stats["duration"]["minutes"]){
                echo "b";
                $ellapsedTime[] = $stats["duration"]["minutes"]." ".Cux::translate("core.debug", "minutes", array(), "Core message, used for writing execution time");
            }
//            if ($stats["duration"]["seconds"]){
                $ellapsedTime[] = $stats["duration"]["seconds"]." ".Cux::translate("core.debug", "seconds", array(), "Core message, used for writing execution time");
//            }
            
            $timeEllapsed = implode(", ", $ellapsedTime);
            
            echo $this->getColoredString(Cux::translate("core.commands", "Ended at:  {endTime}...", array("{endTime}" => $stats["endTime"]), "Message shown while executing console commands"), "light_blue", "black") . "\n";
            echo $this->getColoredString(Cux::translate("core.commands","Finished execution in:  {timeEllapsed}...", array("{timeEllapsed}" => $timeEllapsed), "Message shown while executing console commands"), "light_blue", "black") . "\n";
            echo $this->getColoredString(Cux::translate("core.commands", "Maximum memory used:  {maxMemory}...", array("{maxMemory}" => $stats["peakMemory"]), "Message shown while executing console commands"), "light_blue", "black") . "\n\n";
        }
    }
    
    /**
     * Clear terminal
     */
    protected function clrScr(){
        echo chr(27).chr(91).'H'.chr(27).chr(91).'J';   //^[H^[J  
    }
    
    /**
     * Process interruption/signal handler
     * @param type $sig
     */
    public function scriptKillSignal(int $sig) {
        if (Cux::getInstance()->isConsoleApp()) {
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

    /**
     * Process usleep method
     * @param int $microseconds
     */
    public function usleep(int $microseconds) {
        if (!is_int($microseconds) || $microseconds < 0) {
            if (Cux::getInstance()->isConsoleApp()) {
                echo "\n" . $this->getColoredString("The input parameter '\$microseconds' must be a positive integer", "yellow") . "\n";
            }
            return;
        }
        if (Cux::getInstance()->isConsoleApp()) {
            echo "\n" . $this->getColoredString("Sleeping for $microseconds microseconds", "yellow") . "\n";
        }
        usleep($microseconds);
        clearstatcache();
        gc_collect_cycles();
    }

    /**
     * Process sleep method
     * @param int $seconds
     */
    public function sleep(int $seconds) {
        if (!is_int($seconds) || $seconds < 0) {
            if (Cux::getInstance()->isConsoleApp()) {
                echo "\n" . $this->getColoredString("The input parameter '\$seconds' must be a positive integer", "yellow") . "\n";
            }
            return;
        }
        if (Cux::getInstance()->isConsoleApp()) {
            echo "\n" . $this->getColoredString("Sleeping for $seconds seconds", "yellow") . "\n";
        }
        sleep($seconds);
        clearstatcache();
        gc_collect_cycles();
    }

    /**
     * Returns bash code to print RGB messages (text foreground and background)
     * @param string $string The message to be printed
     * @param string $foreground_color The text color
     * @param string $background_color The text background color
     * @return string
     */
    public function getColoredString(string $string, string $foreground_color = null, string $background_color = null): string {
        
        if (Cux::getInstance()->isWebApp()){
            return $string;
        }
        
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

    /**
     * Getter for the $foreground_colors attribute
     * @return array
     */
    public function getForegroundColors(): array {
        return array_keys($this->foreground_colors);
    }

    /**
     * Getter for the $background_colors attribute
     * @return array
     */
    public function getBackgroundColors(): array {
        return array_keys($this->background_colors);
    }

    /**
     * Output a given message with the execution timestamp
     * @param string $message
     */
    public function printMessage(string $message){
        echo "[ ".date("Y-m-d H:i:s")." ]: ".$message.PHP_EOL;
    }
    
    /**
     * Delete and unset a given variable
     * @param mixed $var
     */
    protected static final function delete(&$var) {
        $var = null;
        unset($var);
        gc_collect_cycles();
    }

    /**
     * Checks if a given variable has a value that is a valid integer, greater than 0
     * @param mixed $var
     * @return bool
     */
    protected final function intOnly($var): bool {
        return (int) $var > 0;
    }

    /**
     * Checks if a given string is a valid IP address
     * @param string $var
     * @return bool
     */
    protected final function ipOnly(string $var): bool {
        return preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $var);
    }

    /**
     * Remove unwanted characters from a given string
     * @param string $name
     * @return string
     */
    protected function normalizeName(string $name): string {

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

    /**
     * Get the DATE from a given timestamp
     * @param string $datetime
     * @return string
     */
    protected function getDate(string $datetime): string{
        return trim($datetime) ? date("Y-m-d", strtotime($datetime)) : " ";
    }
    
    /**
     * Get the TIME from a given timestamp
     * @param string $datetime
     * @return string
     */
    protected function getTime(string $datetime): string{
        return trim($datetime) ? date("H:i:s", strtotime($datetime)) : " ";
    }
    
    /**
     * Remove unwanted characters from a given string
     * @param string $str
     */
    protected function cleanValue(string &$str) {
//        $str = preg_replace('~[^\\pL\d\ ]+~u', '-', $str);
//        $str = trim($str, '-');
        $str = iconv('utf-8', 'us-ascii//TRANSLIT', $str);
//        $str = preg_replace('~[^-\w]+~', '', $str);
        if (empty($str))
            $str = " ";
    }
    
    /**
     * Output memory size in a human readable form
     * @param int $size The memory size
     * @return string
     */
    protected function convert(int $size): string {
        if (!$size) return "0b";
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return sprintf("%.2f %s", $size / pow(1024, ($i = floor(log($size, 1024)))), ' ' . $unit[$i]);
    }
    
    /**
     * Get the SWAP Memory used by the current process
     * @return string
     */
    protected function getSwapMemoryUsage(){
        if (PHP_OS == "Linux"){
            $systemStatus = trim(str_replace("VmSwap: ", "", preg_replace('!\s+!', ' ', shell_exec("grep VmSwap /proc/".$this->pID."/status"))));
            return $systemStatus;
        }
        return "can't do";
    }
    
    /**
     * Swap the values of two variables
     * @param mixed $a
     * @param mixed $b
     */
    protected function swap(&$a, &$b){
        $x = $a;
        $a = $b;
        $b = $x;
        $x = null;
        unset($x);
    }
    
    /**
     * RUN/Execute the Console Command
     * @param array $args The list 
     */
    abstract public function run(array $args);
    
    /**
     * Get the Console Command description & usage info
     * @return string
     */
    public function help(): string{
        $str = "";
        
        $str .= $this->getColoredString("                  CuxCommand BASE                    ", "light_green", "black").PHP_EOL.PHP_EOL;
        $str .= $this->getColoredString("    This is the base class for console commands    ", "blue", "yellow").PHP_EOL;
        
        return $str;
    }

}