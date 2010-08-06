<?
/**
 * Abstract fixture class.
 * 
 * @author pete otaqui
 * @version $Id$
 * @copyright bbc.co.uk, 30 June, 2010
 * @package fixtures
 **/

/**
 * Abstract class which defines a constructor function
 * and some properties.
 * 
 * Abstract classes cannot be instantiated in themselves,
 * but must be extended first.
 * 
 * <code>
 * /**
 *  * This class extends an abstract one.
 *  * 
 *  * @see Fixtures_Abstract_Extends
 *  *\/
 * class Fixtures_Abstract_Extends extends Fixtures_Abstract_Abstract
 * {
 * 
 * }
 * </code>
 **/
abstract class Fixtures_Abstract_Abstract
{
    /**
     * This is a static property - it ought to be overridden
     * by subclasses.
     **/
    public static $staticProperty = "originalAbstractProperty";
    
    /**
     * This is a static property - it ought to be overridden
     * by subclasses and they should have a see tag pointing here
     **/
    public static $staticPropertyWithSee = true;
    
    /**
     * Constructor function.
     *
     * @param string $name 
     */
    public function __construct($name) {
        $this->_name = $name;
    }
    
    /**
     * This method is final, but has no final tag.
     **/
    final public function finalMethod() {
        
    }
}
