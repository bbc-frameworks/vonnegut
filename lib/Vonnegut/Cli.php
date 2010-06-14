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
    
    
    protected $_formats = array('json');
    protected $_format = "json";
    protected $_ask = true;
    
    /**
     * Constructor function, will attempt to parse
     * command line options & arguments and then run.
     *
     * @param string $directory 
     */
    public function __construct() {
        global $argc, $argv;
        $args = $this->parseArgs($argv);
        if ( !isset($args[1]) ) {
            $this->usage($argv[0]);
            $this->_exit();
        } else {
            $this->_ask = ( $args['y'] ) ? false : true;
            if ( isset($args['quiet']) ) $this->log_level = Vonnegut::LOG_LEVEL_CRITICAL;
            if ( isset($args['v']) ) $this->log_level = Vonnegut::LOG_LEVEL_DEBUG;
            if ( isset($args['f']) ) {
                if ( !in_array($args['f'], $this->_formats) ) {
                    $this->_exit(1, "Invalid format '{$args['f']}' specified!");
                }
                $this->_format = $args['f'];
            }
            $this->addDirectory($args[0]);
            $this->_outputDirectory = $args[1];
            if ( !is_dir($this->_outputDirectory) ) {
                if ( $this->_ask ) {
                    $this->log("Output Directory '{$this->_outputDirectory}' does not exist.  Create it? y/n [n]", Vonnegut::LOG_LEVEL_CRITICAL);
                    $yesno = trim(strtolower(fgets(STDIN)));
                    $make_directory = ($yesno == "y" || $yesno == "yes");
                } else {
                    $make_directory = true;
                }
                if ( $make_directory ) {
                    $made_directory = mkdir($this->_outputDirectory);
                    if ( $made_directory == false ) {
                        $this->_exit(1, "Could not make directory {$this->_outputDirectory}!");
                    }
                } else {
                    $this->_exit(1, 'Exiting - output directory doesn\'t exist and won\'t be created.');
                }
            }
            $tree = $this->run();
            $this->_outputTree($tree);
        }
    }
    
    /**
     * Outputs the parsed tree of documentation serializations.
     * 
     * @param object $tree 
     * @return void
     */
    protected function _outputTree($tree) {
        foreach ( $tree as $index=>$item ) {
            $this->_outputTreeItem($index, $item);
        }
    }
    
    /**
     * undocumented function
     *
     * @param object $item 
     * @return void
     */
    protected function _outputTreeItem($index, $item) {
        // May need Vonnegut_Output_Json and Vonnegut_Output_Otherformat
        // classes in the future.
        if ( $this->_format == 'json' ) {
            
        }
    }
    
    /**
     * Prints usage instructions.
     */
    public function usage($filename) {
        $usage = <<<USAGE
Vonnegut PHP docblock parser command line interface
Usage : {$filename} [options] /path/to/php/files /path/to/output

 -f <format>  Output <format>, currently only 'json' is supported
 -v           Verbose console output
 -y           Respond 'yes' to input queries

USAGE;
        $this->log($usage, Vonnegut::LOG_LEVEL_CRITICAL);
    }

    /**
     * Print a line to the console.
     */
    public function log($str, $level = null) {
        $str .= "\n";
        parent::log($str, $level);
    }
    
    /**
     * Exits the application with an optional 
     * status code, message and message log level
     *
     * @return void
     * @author pete otaqui
     **/
    protected function _exit( $code=0, $message=null, $logLevel=null )
    {
        if ( $message !== null ) {
            if ( $code !== 0 && $logLevel == null ) $logLevel = Vonnegut::LOG_LEVEL_CRITICAL;
            $this->log($message, $logLevel);
        }
        exit($code);
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