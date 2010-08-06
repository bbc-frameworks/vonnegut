<?
/**
 * Abstract Extension class.
 * 
 * @author pete otaqui
 * @version $Id$
 * @copyright bbc.co.uk, 30 June, 2010
 * @package fixtures
 **/

/**
 * This class extends an abstract one, and overrides a property
 * 
 * Abstract classes cannot be instantiated in themselves,
 * but must be extended first, like this.
 * 
 **/
class Fixtures_Abstract_Extends extends Fixtures_Abstract_Abstract
{
    
    /**
     * This is an overridden static property.
     **/
    public static $staticProperty = "OVERRIDDENAbstractProperty!";
    
    /**
     * @see Fixtures_Abstract_Abstract::staticPropertyWithSee
     **/
    public static $staticPropertyWithSee = true;
}