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
     * Reflects on a given file, parsing out classes and methods
     * for serialization.
     *
     * @param string $path the path of a file to reflect.
     * @return object the serialized documentation object.
     */
    public function reflectFile($path) { 
        // adding ./ to the beginning of relative paths fixes an issue including php files
        if (! preg_match('/^(?:[a-zA-Z]:\\|\/)/', $path)) {
            $path = '.' . DIRECTORY_SEPARATOR . $path;
        }
        require_once($path);
        $filename = preg_replace("|^.*[\\\/]|", $path, '');
        $serial = new StdClass();
        $serial->path = $path;
        $serial->classes = array();
        $file_reflector = new Zend_Reflection_File($path);
        $classes = $file_reflector->getClasses();
        foreach ( $classes as $class ) {
            $serial->classes[] = $this->reflectClass($class);
        }
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
        $serial->methods = array();
        $methods = $reflection->getMethods();
        foreach ( $methods as $method ) {
            if ( $method->getDeclaringClass()->name !== $reflection->name ) continue;
            $serial->methods[] = $this->reflectMethod($method);
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
    public function reflectMethod($reflection) {
        $serial = new StdClass();
        $serial->name = $reflection->name;
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
        $serial->tags = array();
        if ( $db ) {
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
    
    
}
