<?php


try {
    require_once('BBC/Autoload.php');
    require_once('Zend/Loader/Autoloader.php');
} catch ( Exception $e ) {
    print("You must have the Zend Framework in your PHP include_path.\n");
    print("Current include_path:\n");
    print( ini_get('include_path') );
    die("\n");
}
$autoloader = Zend_Loader_Autoloader::getInstance(); 


require_once(dirname(dirname(__FILE__)).'/lib/Vonnegut.php');

/**
 * Test the basic Vonnegut class
 *
 * @package VonnegutTests
 * @author pete otaqui
 */
class VonnegutTest extends PHPUnit_Framework_Testcase
{
    
    public function testReflectFile() {
        $this->markTestIncomplete('This test is yet to be implemented');
    }
    
    public function testReflectString() {
        $phpString = <<<PHPSTRING
<?php
/**
 * phpString.php file description.
 * 
 * @package VonnegutTests
 * @author Joe Bloggs
 * @see SomethingElse
 */
/**
 * Lorem ipsum dolor sit amet.
 * 
 * Long description would go here and be a bit longer.
 * 
 * @package VonnegutTests
 * @author Joe Bloggs
 * @see SomethingElse
 */
class OneThing
{
    /**
     * Reference to the hoozit.
     * 
     * This is the long description of the hoozit which spans multiple
     * lines.
     * 
     * @var HoozitObject \$hoozit
     */
    protected \$hoozit;
    
    /**
     * Gets a whatsit.
     *
     * @param string \$thingy 
     * @param object \$mabob 
     * @return HoozitObject \$hoozit
     */
    public function whatsit(\$thingy, \$mabob) {
        return \$this->hoozit;
    }
    protected function eck() {
        
    }
}
/**
 * Short description of AndAnother
 * 
 * @package VonnegutTests
 * @author Joe Bloggs
 * @see SomethingElse
 */
class AndAnother
{
    
}
class UndocumentedClass
{
    private function undocumentedMethod(\$param1, \$param2) {
        
    }
}
PHPSTRING;
        $vonnegut = new Vonnegut();
        $serial = $vonnegut->reflectString($phpString);
        $this->assertObjectHasAttribute('path', $serial, 'Vonnegut String (File) Serialization does not contain a Path');
        $this->assertObjectHasAttribute('classes', $serial, 'Vonnegut String (File) Serialization does not contain Classes');
        $this->assertEquals(3, count($serial->classes), 'Vonnegut String (File) Serialization does not contan 3 Classes');
        $this->assertEquals(2, count($serial->classes[0]->methods), 'Vonnegut String (File) Serialization Class does not contan 2 Methods');
        
    }
    
    public function testReflectClass() {
        
    }
    
    public function testReflectMethod() {
        
    }
    
}