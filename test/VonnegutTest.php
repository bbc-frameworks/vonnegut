<?php

require_once('VonnegutTestCase.php');
/**
 * Test the basic Vonnegut class
 *
 * @package VonnegutTests
 * @author pete otaqui
 */
class VonnegutTest extends VonnegutTestCase
{
    
    public function testReflectFile() {
        $this->markTestIncomplete('This test is yet to be implemented');
    }
    
    public function testReflectClass() {
        
    }
    
    public function testReflectMethod() {
        
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
        $vType = "Vonnegut String Serialization";
        $this->assertObjectHasAttribute('path', $serial,                "$vType does not contain a Path attribute");
        $this->assertObjectHasAttribute('classes', $serial,             "$vType does not contain a Classes attribute");
        $this->assertEquals(3, count($serial->classes),                 "$vType does not contain 3 Classes");
        $this->assertObjectHasAttribute('methods', $serial->classes[0], "$vType Class does not contain a Methods attribute");
        $this->assertEquals(2, count($serial->classes[0]->methods),     "$vType Class does not contain 2 Methods");
        $this->assertEquals('Gets a whatsit.', $serial->classes[0]->methods[0]->shortDescription, "OneThing::whatsit method has the wrong short description");
        
    }
}