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
     * Schema version this script generates
     *
     * @var string
     */
    protected $_schemaVersion = "1.0";
    /**
     * Name of this generator to use in the "meta"
     *
     * @var string
     */
    protected $_generator = "Vonnegut";
    /**
     * Language to use in the "meta"
     *
     * @var string
     */
    protected $_language = "php";
    
    
    /**
     * Reflects on a given file, parsing out classes and methods
     * for serialization.
     *
     * @param string $path the path of a file to reflect.
     * @return object the serialized documentation object.
     */
    public function reflectFile($path) { 
        // adding ./ to the beginning of relative paths fixes an issue including php files
        if ( $path[0]!=="/" && !preg_match('/^(?:[a-zA-Z]:\\|\/)/', $path)) {
            $path = '.' . DIRECTORY_SEPARATOR . $path;
        }
        require_once($path);
        $filename = preg_replace("|^.*[\\\/]|", $path, '');
        $serial = new StdClass();
        $serial->constants = array();
        $serial->variables = array();
        $serial->namespaces = array();
        $serial->classes = array();
        $serial->interfaces = array();
        $serial->functions = array();
        $file_reflector = new Zend_Reflection_File($path);
        $serial->tags = array();
        try {
            $db = $file_reflector->getDocBlock();
            $tags = $db->getTags();
            foreach ( $tags as $tag ) {
                $tagSerial = new StdClass();
                $tagSerial->name = $tag->getName();
                $tagSerial->description = $tag->getDescription();
                $serial->tags[] = $tagSerial;
            }
        } catch (Zend_Reflection_Exception $e) {
            $db = false;
        }
        $classes = $file_reflector->getClasses();
        foreach ( $classes as $class ) {
            $classSerial = $this->reflectClass($class);
            $isInterface = $classSerial->interface;
            unset($classSerial->interface);
            if ( $isInterface == false ) {
                $serial->classes[$classSerial->name] = $classSerial;
            } else {
                $serial->interfaces[$classSerial->name] = $classSerial;
            }
            unset($classSerial->name);
        }
        /*
        $functions  = $file_reflector->getFunctions();
        foreach ( $functions as $function ) {
            $functionSerial = $this->reflectMethod($function);
            $serial->functions[$function->name] = $functionSerial;
        }
        */
        $serial->meta = $this->_getMeta();
        $serial->meta->path = $path;
        return $serial;
    }
    
    /**
     * Reflects on a php string (useful for reflecting 'files' not on
     * the local filesystem).
     *
     * @param string $phpString 
     * @return object
     * @author pete otaqui
     */
    public function reflectString($phpString) {
        $path = tempnam(sys_get_temp_dir(), uniqid('__vonnegut__').'.php');
        file_put_contents($path, $phpString);
        return $this->reflectFile($path);
    }
    
    /**
     * Serializes a Class docblock.
     *
     * @param ReflectionClass $reflection 
     * @return void
     * @author pete otaqui
     */
    public function reflectClass($reflection) {
        $serial = new StdClass();
        $serial->name = $reflection->name;
        // put parent class name into $serial->extends
        $parentClass = (array) $reflection->getParentClass();
        if ( array_key_exists('name', $parentClass) ) $serial->extends = $parentClass['name'];
        // abstract / final / interface
        $serial->abstract = $reflection->isAbstract();
        $serial->final = $reflection->isFinal();
        $serial->interface = $reflection->isInterface();
        // put interfaces into $serial->implements
        $serial->implements = $reflection->getInterfaceNames();
        // properties
        $properties = $reflection->getProperties();
        $serial->properties = count($properties) ? array() : new StdClass();
        foreach ( $properties as $property ) {
            $serialProp = new StdClass();
            $serialProp->name = $property->name;
            $serialProp->access = $this->_getAccess($property);
            $serialProp->static = $property->isStatic();
            $serialProp->type = $property->class;
            $serialProp->tags = array();
            if ( $dbProp = $property->getDocComment() ) {
                $serialProp->description = $this->_getDescription($dbProp);
                foreach ($dbProp->getTags() as $tag) {
                    $serialTag = new StdClass();
                    $serialTag->name = $tag->getName();
                    $serialTag->description = $tag->getDescription();
                    $serialProp->tags[] = $serialTag;
                }
            }
            $serial->properties[$serialProp->name] = $serialProp;
            unset($serialProp->name);
        }
        // methods
        $methods = $reflection->getMethods();
        $serial->methods = count($methods) ? array() : new StdClass();
        foreach ( $methods as $method ) {
            if ( $method->getDeclaringClass()->name !== $reflection->name ) continue;
            $serialMethod = $this->reflectMethod($method);
            $serial->methods[$serialMethod->name] = $serialMethod;
            unset($serialMethod->name);
        }
        // constants
        $constants = $reflection->getConstants();
        $serial->constants = count($constants) ? array() : new StdClass();
        foreach ( $constants as $name=>$value ) {
            $constantSerial = new StdClass();
            $constantSerial->name = $name;
            $serial->constants[] = $constantSerial;
        }
        
        // tags
        // weirdly you can't "test" for the presence of a docblock
        // you can only try and access it, and catch thrown exception.
        try {
            $db = $reflection->getDocBlock();
            $serial->description = $this->_getDescription($db);
            foreach ( $db->getTags() as $tag ) {
                $tagSerial = new StdClass();
                $tagSerial->description = $tag->getDescription();
                if ( $tag->getName() == "param" ) {
                    $name = str_replace('$','',$tag->getVariableName());
                    $tagSerial->type = $tag->getType();
                    $tagSerial->access = "public";
                    $tagSerial->magic = true;
                    $serial->properties[$name] = $tagSerial;
                } else {
                    $tagSerial->name = $tag->getName();
                    $serial->tags[] = $tagSerial;
                }
            }
        } catch ( Zend_Reflection_Exception $e ) {
            $db = false;
            $serial->description = "";
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
    public function reflectMethod($reflection) {
        $serial = new StdClass();
        $serial->name = $reflection->name;
        if ( get_class($reflection) == 'Zend_Reflection_Method' ) {            
            $serial->access = $this->_getAccess($reflection);
            $serial->abstract = $reflection->isAbstract();
            $serial->static = $reflection->isStatic();
            $serial->final = $reflection->isFinal();
        }
        // weirdly you can't "test" for the presence of a docblock
        // you can only try and access it, and catch thrown exception.
        try {
            $db = $reflection->getDocBlock();
            $serial->shortDescription = $db->getShortDescription();
            $serial->longDescription = $db->getLongDescription();
        } catch ( Zend_Reflection_Exception $e ) {
            $db = false;
        }
        $serial->tags = array();
        // reflect on parameters first - these can be overridden by tags
        foreach ( $reflection->getParameters() as $parameter ) {
            $paramSerial = new StdClass();
            $paramSerial->name = $parameter->getName();
            if ( $parameter->isArray() ) {
                $paramSerial->type = "array";
            } elseif ( $parameter->getClass() ) {
                $paramSerial->type = $parameter->getClass()->name;
            } else {
                $paramSerial->type = "mixed";
            }
            $paramSerial->allowsNull = $parameter->allowsNull();
            $paramSerial->optional = $parameter->isOptional();
            if ( $parameter->isOptional() && $parameter->isDefaultValueAvailable() ) {
                $paramSerial->defaultValue = $parameter->getDefaultValue();
            }
            $paramSerial->passedByReference = $parameter->isPassedByReference();
            $serial->parameters[] = $paramSerial;
        }
        if ( $db ) {
            $paramCount = 0;
            // tags
            $tags = $db->getTags();
            foreach ( $tags as $tag ) {
                $tagSerial = new StdClass();
                $tagSerial->description = $tag->getDescription();
                // pull out "@return"
                if ( is_a($tag, "Zend_Reflection_Docblock_Tag_Return") ) {
                    $tagSerial->type = $tag->getType();
                    $serial->return = $tagSerial;
                // pull out "@param" and override reflected info
                } elseif ( is_a($tag, "Zend_Reflection_Docblock_Tag_Param") ) {
                    $tagSerial->type = $tag->getType();
                    $tagSerial->name = $tag->getVariableName();
                    if ( isset($serial->parameters[$paramCount]) ) {
                        $tagArray = (array) $tagSerial;
                        foreach ( $tagArray as $k=>$v ) {
                            $serial->parameters[$paramCount]->$k = $v;
                        }
                    } else {
                        $serial->parameters[$paramCount] = $tagSerial;
                    }
                    $paramCount++;
                // put everything else into $serial->tags
                } else {
                    $serial->tags[] = $tagSerial;
                }
            }
        }
        return $serial;
    }
    
    /**
     * gets compound description from shortDescription and longDescription
     *
     * @param ReflectionClass $reflection 
     * @return string description
     */
    protected function _getDescription($reflection) {
        return trim($reflection->getShortDescription() . "\n\n" . $reflection->getLongDescription());
    }
    
    /**
     * gets the access status of a property or method (public, protected, private)
     *
     * @param ReflectionMethod $reflection 
     * @return string one of "public", "private" or "protected"
     */
    protected function _getAccess($reflection) {
        if ( $reflection->isPrivate() ) return "private";
        if ( $reflection->isProtected() ) return "protected";
        if ( $reflection->isPublic() ) return "public";
    }
    
    /**
     * gets a StdClass object containing meta information
     *
     * @return object meta data serial
     * @author pete otaqui
     */
    protected function _getMeta() {
        $meta = new StdClass();
        $meta->generator = $this->_generator;
        $meta->language = $this->_language;
        $meta->schemaVersion = $this->_schemaVersion;
        $meta->date = gmdate('Y-m-d H:i:s');
        return $meta;
    }
    
}
