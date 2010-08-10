<?php
/**
 * Command Line Interface class for Vonnegut.
 * 
 * Currently designed for *nix platforms.
 *
 * @package vonnegut
 * @author pete otaqui
 * @todo Add file exclusions for tree by regex / filename
 * @todo Work out what's breaking this when run on Zend_Test
 * @todo Maybe implement RH's idea for a Vonnegut_PathResolver
 * @todo Add a prefix to strip off filename and serialized 'path' value
 * 
 **/
class Vonnegut_Cli
{
    
    
    const LOG_LEVEL_CRITICAL = 1;
    const LOG_LEVEL_WARN = 6;
    const LOG_LEVEL_DEBUG = 12;
    
    /**
     * Instance of the vonnegut class.
     *
     * @var object
     */
    protected $_vonnegut;
    
    /**
     * Array of acceptable file extensions.
     *
     * @var array
     */
    public $_fileTypes = array('php');
    
    
    /**
     * Log level at which to pass calls to Vonnegut_Cli::log().
     *
     * @var string
     */
    public $log_level = self::LOG_LEVEL_WARN;
    
    /**
     * Array of acceptable output formats.
     * 
     * @var array
     */
    protected $_formats = array('json');
    
    /**
     * Output format
     * @see self::_formats
     *
     * @var string
     */
    protected $_format = "json";
    
    /**
     * Ask for confirmation default is TRUE
     *
     * @var bool
     */
    protected $_ask = true;
    
    /**
     * Array of files to reflect.
     * 
     * @var array
     */
    protected $_files = array();
    
    /**
     * Running in single file mode.
     * 
     * @var $bool
     */
    protected $_singleFile = false;
    
    /**
     * Transact generation (try and read all files before 
     * writing any output)
     *
     * @var bool
     **/
    protected $_transact = false;
    
    /**
     * Directory or file in which to write output.
     *
     * @var string
     **/
    protected $_outputPath;
    
    /**
     * Pretty printing
     * 
     * @var bool
     **/
    protected $_pretty = false;
    
    
    /**
     * Constructor function will attempt to parse
     * command line options & arguments and then run.
     *
     */
    public function __construct() {
        spl_autoload_register("vonnegut_autoload");
        $this->_vonnegut = new Vonnegut();
        global $argc, $argv;
        $args = $this->parseArgs($argv);
        if ( !isset($args[0]) || isset($args['h']) ) {
            $this->usage($argv[0]);
            $this->_exit();
        } else {
            $this->_ask = ( isset($args['y']) ) ? false : true;
            if ( isset($args['quiet']) ) $this->log_level = Vonnegut_Cli::LOG_LEVEL_CRITICAL;
            if ( isset($args['v']) ) $this->log_level = Vonnegut_Cli::LOG_LEVEL_DEBUG;
            if ( isset($args['p']) ) $this->_pretty = true;
            if ( isset($args['f']) ) {
                if ( !in_array($args['f'], $this->_formats) ) {
                    $this->_exit(1, "Invalid format '{$args['f']}' specified!");
                }
                $this->_format = $args['f'];
            }
            if ( is_dir($args[0]) ) {
                $this->addDirectory($args[0]);
            } elseif ( is_file($args[0]) ) {
                if ( $this->_isPhpFile($args[0]) ) {
                    $this->addFile($args[0]);
                    $this->_singleFile = true;
                } else {
                    $file = fopen($args[0],'r');
                    while ( !feof($file) ) {
                        $buffer = fgets($file);
                        $this->addFile($buffer);
                    }
                }
            }
            
            if ( isset($args['o']) ) {
                if ( !is_file($args['o']) && !is_dir($args['o']) ) {
                    $this->_exit(1, 'Exiting - output path doesn\'t exist.');
                }
                $this->_outputPath = preg_replace('/[\\\\\\/]$/', '', $args['o']);
            }
            
            if ( isset($args['t']) ) {
                $this->_transact = true;
            }
            
            $reflections = $this->reflectFiles();
        }
    }
    
    
    /**
     * Adds a directory to the list to be iterated.
     *
     * @param string $directory 
     * @return bool
     */
    public function addDirectory($directory) {
        ini_set('include_path', ini_get('include_path').':'.realpath($directory));
        if ( !is_dir($directory) ) {
            $this->log("Invalid directory {$directory}!", Vonnegut_Cli::LOG_LEVEL_WARN);
            return false;
        } else {
            $this->_recurseDirectory(new RecursiveDirectoryIterator($directory));
        }
    }
    /**
     * Recursive directory function.
     *
     * @param RecursiveDirectoryIterator $iterator 
     */
    protected function _recurseDirectory($iterator) {
        while ($iterator->valid()) {
            if ($iterator->isDir() && !$iterator->isDot()) {
                if ($iterator->hasChildren()) {
                    $this->_recurseDirectory($iterator->getChildren());
                }
            } elseif ($iterator->isFile()) {
                $path = $iterator->getPath() . '/' . $iterator->getFilename();
                $pathinfo = pathinfo($path);
                if ( $this->_isPhpFile($path) ) {
                    $this->addFile($path);
                }
            }
            $iterator->next();
        }
    }
    
    /**
     * Adds a file to the list to be iterated.
     *
     * @param string $file 
     * @return bool True if the file exists and was PHP false otherwise.
     */
    public function addFile($file) {
        if ( !is_file($file) || !$this->_isPhpFile($file) ) {
            $this->log("Invalid file {$file}!", Vonnegut_Cli::LOG_LEVEL_WARN);
            return false;
        }
        $this->_files[] = $file;
        return true;
    }
    
    /**
     * Reflects an array of files.  If no argument provided will
     * use $this->_files;
     * 
     * @param array $files the array of files to relfect (optional)
     * @return array $reflections an array of serialized reflections
     */
    public function reflectFiles($files = null) {
        if ( $files == null ) $files = $this->_files;
        $reflections = array();
        foreach ( $files as $file ) {
            $this->log("Reading $file");
            $reflection = $this->_vonnegut->reflectFile($file);
            if ( !$this->_transact ) {
                $this->_outputReflectionFile($file, $reflection);
            }
            $reflections[$file] = $reflection;
        }
        if ( $this->_transact ) {
            $this->_outputReflectionFiles($reflections);
        }
        return $reflections;
    }
    
    
    
    /**
     * Outputs a set of reflections.
     * 
     * @param array $reflections array in the form (filename=>reflection)
     * @return void
     */
    protected function _outputReflectionFiles($reflections) {
        $topLevelMembers = array('classes', 'interfaces', 'functions', 'variables', 'constants', 'namespaces');
        $serial = new StdClass();
        $serial->classes = new StdClass();
        $serial->interfaces = new StdClass();
        $serial->functions = array();
        $serial->constants = array();
        $serial->variables = array();
        $serial->namespaces = new StdClass();
        foreach ( $reflections as $reflection ) {
            foreach  ($topLevelMembers as $member) {
                if ( is_array($reflection->{$member}) ) {
                    if ( isset($reflection->{$member}) ) $serial->{$member} += $reflection->{$member};
                } elseif ( is_object($reflection->{$member}) ) {
                    $arrRef = (array) $reflection->{$member};
                    foreach  ($arrRef as $k=>$v) {
                        $serial->{$member}->$k = $v;
                    }
                }
            }
        }
        $namespace = new StdClass();
        $namespace->namespace = $serial;
        //print_r($serial);
        $this->_outputReflectionFile("vonnegut", $serial);
    }
    
    /**
     * Outputs the reflection - can be to the command line or a file
     * or a directory.
     *
     * @param string $infile
     * @param object $reflection 
     * @return void
     */
    protected function _outputReflectionFile($infile, $reflection) {
        // May need Vonnegut_Output_Json and Vonnegut_Output_Otherformat classes
        // in the future.
        if ( $this->_format == 'json' ) {
            $json = Zend_Json::encode($reflection);
            $output = ($this->_pretty) ? Zend_Json::prettyPrint($json) : $json;
            if ( $this->_outputPath ) {
                if ( $this->_singleFile ) {
                    $filepath = $this->_outputPath;
                } else {
                    $filename = str_replace(DIRECTORY_SEPARATOR, '_', $infile);
                    $filepath = $this->_outputPath . DIRECTORY_SEPARATOR . $filename . '.json';
                }
                $this->log("Writing $filepath");
                $file = fopen($filepath,'w');
                $rote = fwrite($file, $output);
                if ( $rote === false ) {
                    $this->log("Could not write $filename", Vonnegut_Cli::LOG_LEVEL_WARN);
                }
            } else {
                $this->log($output, Vonnegut_Cli::LOG_LEVEL_CRITICAL);
            }
        }
    }
    
    /**
     * Checks for a PHP file by using the extension.
     *
     * @param string $path 
     * @return bool
     */
    protected function _isPhpFile($path) {
        $pathinfo = pathinfo($path);
        $isPhpFile = (
            isset($pathinfo['extension']) &&
            in_array(strtolower($pathinfo['extension']), $this->_fileTypes)
        );
        return $isPhpFile;
    }
    
    /**
     * Prints usage instructions.
     * 
     * @param string $executable the name of the executable file.
     */
    public function usage($executable) {
        $usage = <<<USAGE
Vonnegut PHP docblock parser command line interface
Usage : {$executable} [options] /tree/of/php/files/
        {$executable} [options] /single/php/file.php
        {$executable} [options] /one/php/file/per/line.txt

 -f=<format>  Output <format>, currently only 'json' is supported.
 -h           Print help (this message) and exit.
 -o=<path>    Write output to <path>.  Should be a directory if
              reflecting multiple files.  Default is STDOUT.
 -t           "Transaction", parse all files before outputting. This
              will be required for @see and other post-processing.
 -v           Verbose console output.
 -y           Respond 'yes' to input queries.

USAGE;
        $this->log($usage, Vonnegut_Cli::LOG_LEVEL_CRITICAL);
    }

    /**
     * Print a line to the console.
     * 
     * @var string $str messaage to print
     * @var integer $level message level to print
     * @todo Use STDERR for error messages!
     */
    public function log($str, $level = null) {
        $str .= "\n";
        if ( $level == null ) $level = self::LOG_LEVEL_DEBUG;
        if ( $level <= $this->log_level ) {
            print $str;
        }
    }
    
    /**
     * Exits the application with an optional 
     * status code message and message log level
     * 
     * @param integer $code exit status code (0=ok >1=error)
     * @param string $message message to print.  Will print regardless 
     * of verbosity if $code>0 and logLevel not supplied.
     * @param integer $logLevel logLevel for this message
     * @return void
     * @todo Use STDERR for error messages!
     **/
    protected function _exit( $code=0, $message=null, $logLevel=null )
    {
        if ( $message !== null ) {
            if ( $code !== 0 && $logLevel == null ) $logLevel = Vonnegut_Cli::LOG_LEVEL_CRITICAL;
            $this->log($message, $logLevel);
        }
        exit($code);
    }
    

    /**
     * Command line option parsing function.
     * 
     * @link http://pwfisher.com/nucleus/index.php?itemid=45
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


function vonnegut_autoload ($classname) {
    if (! class_exists($classname, false)) {
        $path = str_replace('_', DIRECTORY_SEPARATOR, $classname);
        require_once($path . '.php');
    }
}