<?
/**
 * Command Line Interface class for Vonnegut.
 * 
 * Currently designed for *nix platforms.
 *
 * @package Vonnegut
 * @author pete otaqui
 **/
class Vonnegut_Cli extends Vonnegut
{
    
    /**
     * Constructor function, if a directory path is 
     * passed as the first argument, will run immediately.
     *
     * @param string $directory 
     */
    public function __construct() {
        global $argc, $argv;
        if ( $argc < 3 ) {
            $this->usage($argv[0]);
        } else {
            $args = $this->parseArgs($argv);
            if ( $args['v'] ) $this->_log_level = Vonnegut::LOG_LEVEL_DEBUG;
            $this->addDirectory($args[0]);
            $tree = $this->run();
            //print_r($tree);
        }
    }
    
    
    public function usage($filename) {
        $usage = <<<USAGE
Vonnegut PHP docblock parser command line interface
Usage : {$filename} [options] /path/to/php/files /path/to/output

 -f <format>  Output <format>, currently only 'json' is supported
 -v           Verbose console output

USAGE;
        $this->log($usage);
    }



    /**
     * Print a line to the console.
     */
    public function log($str, $level = null) {
        $str .= "\n";
        parent::log($str, $level);
    }

    /**
     * Command line option parsing function.
     * 
     * @see http://pwfisher.com/nucleus/index.php?itemid=45
     */
    public function parseArgs($argv){
        array_shift($argv); $o = array();
        foreach ($argv as $a){
            if (substr($a,0,2) == '--'){ $eq = strpos($a,'=');
                if ($eq !== false){ $o[substr($a,2,$eq-2)] = substr($a,$eq+1); }
                else { $k = substr($a,2); if (!isset($o[$k])){ $o[$k] = true; } } }
            else if (substr($a,0,1) == '-'){
                if (substr($a,2,1) == '='){ $o[substr($a,1,1)] = substr($a,3); }
                else { foreach (str_split(substr($a,1)) as $k){ if (!isset($o[$k])){ $o[$k] = true; } } } }
            else { $o[] = $a; } }
        return $o;
    }
}