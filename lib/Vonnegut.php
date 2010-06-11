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
     * @return void
     */
    public function addDirectory($directory) {
        array_push($this->_directories, $directory);
    }
    
    /**
     * Run the documentation generation routine.
     *
     * @return void
     */
    public function run() {
        foreach ( $this->_directories as $dir ) {
            $this->_recurseTree(new recursiveDirectoryIterator($dir));
        }
    }
    
    /**
     * Recursive function to iterate a directory, and reflect valid files.
     *
     * @param RecursiveDirectoryIterator $iterator
     * @return void
     */
    protected function _recurseTree($iterator) {
        while ($iterator->valid()) {
            if ($iterator->isDir() && !$iterator->isDot()) {
                if ($iterator->hasChildren()) {
                    $this->_recurseTree($iterator->getChildren());
                }
            }
            else if ($iterator->isFile()) {
                $path = $iterator->getPath() . '/' . $iterator->getFilename();
                $pathinfo = pathinfo($path);
                if (
                    isset($pathinfo['extension']) &&
                    in_array(strtolower($pathinfo['extension']), $this->types)
                ) {
                    $this->reflectFile($path);
                }
            }
            $iterator->next();
        }
    }
    
    /**
     * Reflects on a given file, parsing out classes and methods
     * for serialization.
     *
     * @param string $path the path of a file to reflect.
     * @return object $doc the serialized documentation object.
     */
    public function reflectFile($path) {
        $filename = (strpos($path,"/")!==false) ? preg_replace("|.*/(.+)$|",$path,"$1") : $path;
        $doc = new StdClass();
        $doc->path = $path;
        $doc->classes = array();
        $file_reflector = new Zend_Reflection_File($path);
        $classes = $file_reflector->getClasses();
        foreach ( $classes as $class ) {
            $classOutput = $this->_serializeDocBlock($class,"class");
            $classOutput->methods = array();
            $methods = $class->getMethods();
            foreach ( $methods as $method ) {
                if ( $method->getDeclaringClass()->name !== $class->name ) continue;
                $methodOutput = $this->_serializeDocBlock($method,"method");
                array_push($classOutput->methods, $methodOutput);
            }
            $doc->classes[] = $classOutput;
        }
        return $doc;
    }
    
    /**
     * undocumented function
     *
     * @param string $reflection 
     * @param string $type 
     * @return void
     * @author pete otaqui
     */
    protected function _serializeDocBlock($reflection,$type) {
        $serial = new StdClass();
        $serial->name = $reflection->name;
        if ( $type == "class" ) {
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
        } elseif ( $type == "method" ) {
            if ( $reflection->isPrivate() ) $serial->access = "private";
            if ( $reflection->isProtected() ) $serial->access = "protected";
            if ( $reflection->isPublic() ) $serial->access = "public";
            try {
                $serial->body = $reflection->getContents(true);
            } catch ( Zend_Reflection_Exception $e ) {
                $serial->body = $reflection->getContents(false);
            }
        }
        try {
            $db = $reflection->getDocBlock();
            $serial->shortDescription = $db->getShortDescription();
            $serial->longDescription = $db->getLongDescription();
            if ( $type == "method" ) {
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
        } catch ( Zend_Reflection_Exception $e ) {
            
        }
        return $serial;
    }
    
    
    
    
}
