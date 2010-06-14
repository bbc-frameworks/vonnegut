<?php
/**
 * Class which uses the Zend_Reflection API for parsing PhpDoc comments.
 * 
 * @package vonnegut
 * @author Pete Otaqui <pete.otaqui@bbc.co.uk>
 * @version $Rev$
 */
class Vonnegut
{
    
    const LOG_LEVEL_CRITICAL = 1;
    const LOG_LEVEL_WARN = 6;
    const LOG_LEVEL_DEBUG = 12;
    
    /**
     * Log level at which to pass calls to Vonnegut::log().
     *
     * @var string
     */
    public $log_level = self::LOG_LEVEL_WARN;
    
    /**
     * Array of acceptable file extensions.
     *
     * @var string
     */
    public $types = array('php');
    
    /**
     * Array of directories to iterate.
     *
     * @var string
     */
    protected $_directories = array();
    
    
    /**
     * Constructor function, if a directory path is 
     * passed as the first argument, will run immediately.
     *
     * @param string $directory 
     */
    public function __construct($directory = null) {
        if ( $directory !== null ) {
            $this->addDirectory($directory);
            $this->run();
        }
    }
    
    /**
     * Adds a directory to the list to be iterated.
     *
     * @param string $directory 
     * @return boolean
     */
    public function addDirectory($directory) {
        if ( !is_dir($directory) ) {
            $this->log("Invalid directory {$directory}!", Vonnegut::LOG_LEVEL_WARN);
            return false;
        }
        array_push($this->_directories, $directory);
        return true;
    }
    
    /**
     * Run the documentation generation routine.
     *
     * @return void
     */
    public function run() {
        $list = array();
        foreach ( $this->_directories as $dir ) {
            $list += $this->reflectTree(new RecursiveDirectoryIterator($dir));
        }
        return $list;
    }
    
    /**
     * Recursive function to iterate a directory, and reflect valid files.
     *
     * @param RecursiveDirectoryIterator $iterator
     * @return void
     */
    public function reflectTree($iterator, &$tree = array()) {
        while ($iterator->valid()) {
            if ($iterator->isDir() && !$iterator->isDot()) {
                if ($iterator->hasChildren()) {
                    $this->reflectTree($iterator->getChildren(), $tree);
                }
            }
            else if ($iterator->isFile()) {
                $path = $iterator->getPath() . '/' . $iterator->getFilename();
                $pathinfo = pathinfo($path);
                if (
                    isset($pathinfo['extension']) &&
                    in_array(strtolower($pathinfo['extension']), $this->types)
                ) {
                    $tree[$path] = $this->reflectFile($path);
                }
            }
            $iterator->next();
        }
        return $tree;
    }
    
    /**
     * Reflects on a given file, parsing out classes and methods
     * for serialization.
     *
     * @param string $path the path of a file to reflect.
     * @return object $doc the serialized documentation object.
     */
    public function reflectFile($path) {
        $this->log("Reflecting file $path");
        require_once($path);
        $filename = (strpos($path,"/")!==false) ? preg_replace("|.*/(.+)$|",$path,"$1") : $path;
        $doc = new StdClass();
        $doc->path = $path;
        $doc->classes = array();
        $file_reflector = new Zend_Reflection_File($path);
        $classes = $file_reflector->getClasses();
        foreach ( $classes as $class ) {
            $classOutput = $this->serializeDocBlockClass($class);
            $classOutput->methods = array();
            $methods = $class->getMethods();
            foreach ( $methods as $method ) {
                if ( $method->getDeclaringClass()->name !== $class->name ) continue;
                $methodOutput = $this->serializeDocBlockMethod($method);
                array_push($classOutput->methods, $methodOutput);
            }
            $doc->classes[] = $classOutput;
        }
        return $doc;
    }
    
    /**
     * Serializes a Class docblock.
     *
     * @param ReflectionClass $reflection 
     * @return void
     * @author pete otaqui
     */
    public function serializeDocBlockClass($reflection) {
        $serial = new StdClass();
        $serial->name = $reflection->name;
            $this->log(" class ".$reflection->name);
            $properties = $reflection->getProperties();
            $serial->properties = array();
            foreach ( $properties as $property ) {
                $serialProp = new StdClass();
                $serialProp->name = $property->name;
                if ( $dbProp = $property->getDocComment() ) {
                    $serialProp->shortDescription = $dbProp->getShortDescription();
                    $serialProp->longDescription = $dbProp->getLongDescription();
                }
                $serial->properties[] = $serialProp;
            }
        try {
            $db = $reflection->getDocBlock();
            $serial->shortDescription = $db->getShortDescription();
            $serial->longDescription = $db->getLongDescription();
        } catch ( Zend_Reflection_Exception $e ) {
            
        }
        return $serial;
    }
    
    /**
     * Serializes a Method docblock.
     *
     * @param ReflectionMethod $reflection 
     * @return object
     * @author pete otaqui
     */
    public function serializeDocBlockMethod($reflection) {
        $serial = new StdClass();
        $serial->name = $reflection->name;
        $this->log("  method ".$reflection->name);
        if ( $reflection->isPrivate() ) $serial->access = "private";
        if ( $reflection->isProtected() ) $serial->access = "protected";
        if ( $reflection->isPublic() ) $serial->access = "public";
        try {
            $db = $reflection->getDocBlock();
            $serial->shortDescription = $db->getShortDescription();
            $serial->longDescription = $db->getLongDescription();
            $serial->body = $reflection->getContents(true);
        } catch ( Zend_Reflection_Exception $e ) {
            $serial->body = $reflection->getContents(false);
            $db = false;
        }
        if ( $db ) {
            $serial->tags = array();
            $tags = $db->getTags();
            foreach ( $tags as $tag ) {
                $tagSerial = new StdClass();
                $tagSerial->name = $tag->getName();
                $tagSerial->description = $tag->getDescription();
                if ( is_a($tag, "Zend_Reflection_Docblock_Tag_Return") ) {
                    $tagSerial->type = $tag->getType();
                } elseif ( is_a($tag, "Zend_Reflection_Docblock_Tag_Param") ) {
                    $tagSerial->type = $tag->getType();
                    $tagSerial->variableName = $tag->getVariableName();
                }
                $serial->tags[] = $tagSerial;
            }
        }
        return $serial;
    }
    
    
    
    /**
     * Print a log message;
     */
    public function log($str,$level=null) {
        if ( $level == null ) $level = self::LOG_LEVEL_DEBUG;
        if ( $level <= $this->log_level ) {
            print $str;
        }
    }
    
    
    
}
