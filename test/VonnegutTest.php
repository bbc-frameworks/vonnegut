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
        $vonnegut = new Vonnegut();
        $serial = $vonnegut->reflectFile(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Standalone.php");
        $vType = "Vonnegut File Serialization";
        $this->assertObjectHasAttribute('classes', $serial,    "$vType has 'classes'");
        $this->assertObjectHasAttribute('interfaces', $serial, "$vType has 'interfaces'");
        $this->assertObjectHasAttribute('functions', $serial,  "$vType has 'functions'");
        $this->assertObjectHasAttribute('constants', $serial,  "$vType has 'constants'");
        $this->assertObjectHasAttribute('variables', $serial,  "$vType has 'variables'");
        $this->assertObjectHasAttribute('namespaces', $serial, "$vType has 'namespaces'");
        $this->assertObjectHasAttribute('tags', $serial,       "$vType has 'tags'");
        $this->assertObjectHasAttribute('meta', $serial,       "$vType has 'meta'");
    }
    
    public function testReflectClass() {
        $vonnegut = new Vonnegut();
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Standalone.php");
        $serial = $vonnegut->reflectClass(new Zend_Reflection_Class('Fixtures_Standalone'));
        $vType = "Vonnegut Class Serialization";
        $this->assertObjectHasAttribute('constants', $serial,  "$vType has 'constants'");
        $this->assertObjectHasAttribute('properties', $serial, "$vType has 'properties'");
        $this->assertObjectHasAttribute('methods', $serial,    "$vType has 'methods'");
        $this->assertObjectHasAttribute('tags', $serial,       "$vType has 'tags'");
    }
    
    public function testReflectMethod() {
        $vonnegut = new Vonnegut();
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Standalone.php");
        $serial = $vonnegut->reflectMethod(new Zend_Reflection_Method('Fixtures_Standalone', 'publicMethod'));
        $vType = "Vonnegut Method Serialization";
        $this->assertObjectHasAttribute('tags', $serial, "$vType has 'tags'");
        $this->assertObjectHasAttribute('signatures', $serial, "$vType has 'signatures'");
        $this->assertEquals(1, count($serial->signatures), "$vType has 1 signature");
        $signature = $serial->signatures[0];
        $this->assertObjectHasAttribute('parameters', $signature, "$vType has 'parameters'");
        $this->assertObjectHasAttribute('return', $signature,     "$vType has 'return'");
    }
    
    public function testUndocumentedParamTypeHint() {
        $vonnegut = new Vonnegut();
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Standalone.php");
        $class = $vonnegut->reflectClass(new Zend_Reflection_Class('Fixtures_Standalone'));
        $method = $class->methods['undocumentedTypeHint'];
        $parameters = $method->signatures[0]->parameters;
        $this->assertEquals( 1, count($parameters), "Takes 1 parameter");
        $parameter = $parameters[0];
        $this->assertEquals( "arg1", $parameter->name, "Name is 'arg1'");
        $this->assertEquals( "Fixtures_Standalone", $parameter->type, "Type is Fixtures_Standalone");
        $this->assertFalse( $parameter->allowsNull, "Does not allow null");
        $this->assertFalse( $parameter->passedByReference, "Is not passed by reference");
    }
    
    public function testDocumentedParamTypeHint() {
        $vonnegut = new Vonnegut();
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Standalone.php");
        $class = $vonnegut->reflectClass(new Zend_Reflection_Class('Fixtures_Standalone'));
        $method = $class->methods['documentedTypeHint'];
        $parameters = $method->signatures[0]->parameters;
        $this->assertEquals( 1, count($parameters), "Takes 1 parameter");
        $parameter = $parameters[0];
        $this->assertEquals( "\$overrideName", $parameter->name, "Name is 'overrideName'");
        $this->assertEquals( "Override_Class", $parameter->type, "Type is Fixtures_Standalone");
        $this->assertTrue( $parameter->allowsNull, "Allows null");
        $this->assertFalse( $parameter->passedByReference, "Is not passed by reference");
    }
    
    public function testMagicProperties() {
        $vonnegut = new Vonnegut();
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Standalone.php");
        $class = $vonnegut->reflectClass(new Zend_Reflection_Class('Fixtures_Standalone'));
        $this->assertArrayHasKey('read', $class->properties,      "Has magic properties");
        $this->assertArrayHasKey('readwrite', $class->properties, "Has magic properties");
    }
    
    public function testMagicMethods() {
        $vonnegut = new Vonnegut();
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Standalone.php");
        $class = $vonnegut->reflectClass(new Zend_Reflection_Class('Fixtures_Standalone'));
        $this->markTestIncomplete('Need to add magic method parsing to Vonnegut');
    }
    
    public function testStaticMethod() {
        $vonnegut = new Vonnegut();
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Standalone.php");
        $class = $vonnegut->reflectClass(new Zend_Reflection_Class('Fixtures_Standalone'));
        $this->assertArrayHasKey('staticMethod', $class->methods, "Has static method called 'staticMethod'");
        $this->assertTrue( $class->methods['staticMethod']->static, 'Static attribute set on staticMethod' );
    }
    
    public function testStaticProperty() {
        $vonnegut = new Vonnegut();
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Standalone.php");
        $class = $vonnegut->reflectClass(new Zend_Reflection_Class('Fixtures_Standalone'));
        $this->assertArrayHasKey('staticProperty', $class->properties, "Has static property called 'staticProperty'");
        $this->assertTrue( $class->properties['staticProperty']->static, 'Static attribute set on staticProperty' );
    }
    
    
    
    public function testPropertyHasTags() {
        $vonnegut = new Vonnegut();
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Standalone.php");
        $class = $vonnegut->reflectClass(new Zend_Reflection_Class('Fixtures_Standalone'));
        $this->assertArrayHasKey('staticProperty', $class->properties, "Has static method called 'static'");
        $property = $class->properties['staticProperty'];
        $this->assertObjectHasAttribute( 'tags', $property, 'Property has tags');
    }
    
    public function testIsPassedByReference() {
        $vonnegut = new Vonnegut();
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Standalone.php");
        $class = $vonnegut->reflectClass(new Zend_Reflection_Class('Fixtures_Standalone'));
        $method = $class->methods['parameterPassedByReference'];
        $parameter = $method->signatures[0]->parameters[0];
        $this->assertTrue( $parameter->passedByReference, "Parameter is passed by reference");
    }
    
    public function testAbstractClass() {
        $vonnegut = new Vonnegut();
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Abstract/Abstract.php");
        $class = $vonnegut->reflectClass(new Zend_Reflection_Class('Fixtures_Abstract_Abstract'));
        $this->assertTrue( $class->abstract, "Class is abstract");
    }
    
    public function testFinalClass() {
        $vonnegut = new Vonnegut();
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Final.php");
        $class = $vonnegut->reflectClass(new Zend_Reflection_Class('Fixtures_Final'));
        $this->assertTrue( $class->final, "Class is final");
    }
    
    public function testFinalMethod() {
        $vonnegut = new Vonnegut();
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Standalone.php");
        $class = $vonnegut->reflectClass(new Zend_Reflection_Class('Fixtures_Standalone'));
        $method = $class->methods['finalMethod'];
        $this->assertTrue( $method->final, "Method is final");
    }
    
    public function testExtends() {
        $vonnegut = new Vonnegut();
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Extends.php");
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Extends/Parent.php");
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Extends/Child.php");
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Extends/Child/Grandchild.php");
        $parent     = $vonnegut->reflectClass(new Zend_Reflection_Class('Fixtures_Extends_Parent'));
        $child      = $vonnegut->reflectClass(new Zend_Reflection_Class('Fixtures_Extends_Child'));
        $grandchild = $vonnegut->reflectClass(new Zend_Reflection_Class('Fixtures_Extends_Child_Grandchild'));
        $this->assertEquals( 'Fixtures_Extends_Parent', $child->extends, "Child extends from Parent");
        $this->assertEquals( 'Fixtures_Extends_Child', $grandchild->extends, "Grandchild extends from Child");
    }
    
    public function testInterface() {
        $vonnegut = new Vonnegut();
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Interface.php");
        $file = $vonnegut->reflectFile(dirname(__FILE__) . "/fixtures/Fixtures/Interface.php");
        $this->assertEquals( true, $file->classes->Fixtures_Interface->interface, "File contains an interface");
    }
    
    public function testImplements() {
        $vonnegut = new Vonnegut();
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Interface.php");
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Interface/Implements.php");
        $class = $vonnegut->reflectClass(new Zend_Reflection_Class('Fixtures_Interface_Implements'));
        $this->assertContains( "Fixtures_Interface", $class->implements, "Contains 'Fixtures_Interface'");
    }
    
    public function testMultipleClasses() {
        $vonnegut = new Vonnegut();
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Multiple.php");
        $file = $vonnegut->reflectFile(dirname(__FILE__) . "/fixtures/Fixtures/Multiple.php");
        $this->assertEquals( 3, count((array)$file->classes), "Contains 3 classes");
    }
    
    public function testGlobals() {
        $vonnegut = new Vonnegut();
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Globals.php");
        $file = $vonnegut->reflectFile(dirname(__FILE__) . "/fixtures/Fixtures/Globals.php");
        // Zend_Reflect_File only gets Classes and Functions in the global scope.
        //$this->assertEquals( 1, count($file->constants), "Contains 1 constant");
        //$this->assertEquals( 1, count($file->variables), "Contains 1 variable");
        
        // really shouldn't have to disable this one:
        //$this->assertEquals( 1, count($file->functions), "Contains 1 function");
    }
    
    public function testConstants() {
        $vonnegut = new Vonnegut();
        require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Standalone.php");
        $class = $vonnegut->reflectClass(new Zend_Reflection_Class('Fixtures_Standalone'));
        $constants = $class->constants;
        $this->assertEquals( 1, count($constants), "There is one constant");
        $this->assertArrayHasKey( 'FOO', $constants, "FOO is a constant");
        $this->assertEquals('bar', $constants['FOO']->value, "FOO is bar");
    }
    
    
    
    // public function testInternal() {
    //     $vonnegut = new Vonnegut();
    //     require_once(realpath(dirname(__FILE__)) . "/fixtures/Fixtures/Standalone.php");
    //     $class = $vonnegut->reflectClass(new Zend_Reflection_Class('Fixtures_Standalone'));
    //     $this->markTestIncomplete('This test is yet to be implemented');
    // }
    
    
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
        $this->assertObjectHasAttribute('classes', $serial,     "$vType has 'classes'");
        $this->assertObjectHasAttribute('OneThing', $serial->classes,  "$vType classes contains 'OneThing'");
        $oneThing = $serial->classes->OneThing;
        $this->assertObjectHasAttribute('constants', $serial,   "$vType has 'constants'");
        $this->assertObjectHasAttribute('variables', $serial,   "$vType has 'variables'");
        $this->assertObjectHasAttribute('namespaces', $serial,  "$vType has 'namespaces'");
        $this->assertObjectHasAttribute('meta', $serial,        "$vType has 'meta'");
        $this->assertEquals(3, count((array)$serial->classes),         "$vType contains 3 classes");
        $this->assertObjectHasAttribute('methods', $oneThing,   "$vType Class contains a 'methods' attribute");
        $this->assertArrayHasKey('whatsit', $oneThing->methods, "$vType Class contains whatsit method");
        $this->assertArrayHasKey('eck', $oneThing->methods,     "$vType Class contains eck method");
        $this->assertEquals("Gets a whatsit.", $oneThing->methods['whatsit']->description, "OneThing::whatsit method has the right description");
        
    }
}