<?

class Vonnegut_Namespace {
    public $name;
    public $classes;
    public $constants;
    public $functions;
    public $interfaces;
    public $namespaces;
    public $variables;
    
    public function __construct($name = null) {
        $this->name = $name;
        $this->classes = new StdClass();
        $this->constants = new StdClass();
        $this->functions = new StdClass();
        $this->interfaces = new StdClass();
        $this->namespaces = new StdClass();
        $this->variables = new StdClass();
    }
    
}