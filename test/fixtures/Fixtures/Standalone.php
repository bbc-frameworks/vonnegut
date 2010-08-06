<?
/**
 * This file contains a standalone class in it's own package.
 * 
 * @author pete otaqui
 * @version $Id$
 * @copyright bbc.co.uk, 30 June, 2010
 * @package standalone
 **/


/**
 * Standalone class in it's own package.
 * 
 * @param mixed $readwrite a readable and writable magic property
 * @param string $read a readble magic property
 * @param mixed $write a writable magic property
 * 
 * @package standalone
 * @author pete otaqui
 */
class Fixtures_Standalone
{
    
    /**
     * FOO is a constant.
     */
    const FOO = 'bar';
    
    
    /**
     * Static property.
     * 
     * Static properties are called on the class.
     * 
     * @example $foo = Fixtures_Standalone::static;
     */
    static $staticProperty = "staticProperty";
    
    /**
     * Static method.
     * 
     * Static methods are called on the class.
     * 
     * @example if ( Fixtures_Standalone::staticMethod() ) echo "yay!";
     * @param Fixtures_Standalone $arg1 an instance of this class.
     * @return boolean
     */
    public static function staticMethod(Fixtures_Standalone $arg1) {
        return true;
    }
    
    /**
     * Undocumented type-hinting.
     * 
     * This static method uses type hinting for the param, but does
     * not have any documentation for type.
     * 
     * @return boolean
     */
    public static function undocumentedTypeHint(Fixtures_Standalone $arg1) {
        return true;
    }
    
    /**
     * Documented type-hinting.
     * 
     * This static method uses type hinting for the param, and has
     * some overriding documentation
     * 
     * @param Override_Class $overrideName Some values are overridden 
     * @return boolean
     */
    public static function documentedTypeHint(Fixtures_Standalone $arg1=null) {
        return true;
    }
    
    /**
     * Public property.
     *
     * @var array
     */
    public $public = array();
    
    /**
     * Public property which is not defined with a type.
     */
    public $publicMixed;
    
    /**
     * Public method.
     * 
     * A public method can be thought of by user's of the class as it's API.
     * <code>
     * // example 1
     * $instance = new FixtureSingleClass();
     * $result = $instance->publicMethod('arg1');
     * </code>
     * <code>
     * // example 2
     * $instance = new FixtureSingleClass();
     * $result = $instance->publicMethod('arg1', true);
     * </code>
     * 
     * @example $result = $fixtureSingleClassInstance->publicMethod('foo');
     * @param mixed $arg1 A required parameter, which can be different types.
     * @param bool $arg2 optional An optional boolean parameter, default is false.
     * @return array|string|object
     * @author pete otaqui
     **/
    public function publicMethod($arg1, $arg2 = false) {
        
    }
    
    /**
     * It's a good thing this is deprecated, because it
     * doesn't do anything - although it's short description
     * does span a few lines.
     * 
     * But it's long description is quite short!
     * 
     * @deprecated
     * @return void
     * @author pete otaqui
     **/
    public function deprecatedMethod () {}
    
    /**
     * Final method
     *
     * @return void
     */
    final public function finalMethod() {}
    
    /**
     * Takes a parameter passed by reference
     *
     * @param string $param 
     * @return void
     */
    public function parameterPassedByReference(&$param) {
        unset($param);
    }
    
    
    /**
     * @internal
     */
    protected $_readwrite;
    /**
     * @internal
     */
    protected $_write;
    
    /**
     * @internal
     */
    public function __get($key) {
        switch ($key) {
            case 'read' :
                return "you read me like a book!";
            case 'readwrite' :
                return $this->_readwrite;
            break;
        }
    }
    /**
     * @internal
     */
    public function __set($key, $val) {
        switch ($key) {
            case 'write' :
                $this->_write = $val;
            case 'readwrite' :
                $this->_readwrite = $val;
            break;
        }
    }
    
    /**
     * @internal
     */
    public function __call($method, $args) {
        switch ($method) {
            case 'magicMethod' : 
                return (object) $args;
            break;
        }
    }
    
}



