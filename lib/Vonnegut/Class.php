<?

class Vonnegut_Class {
    public $name = "";
    public $constants;
    public $methods;
    public $properties;
    public $abstract = false;
    public $final = false;
    public $interface = false;
    
    public function __construct() {
        $this->constants = new StdClass();
        $this->methods = new StdClass();
        $this->properties = new StdClass();
    }
    
}