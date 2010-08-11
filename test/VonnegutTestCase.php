<?



require_once(dirname(dirname(__FILE__)).'/lib/Vonnegut.php');
require_once(dirname(dirname(__FILE__)).'/lib/Vonnegut/Class.php');
require_once(dirname(dirname(__FILE__)).'/lib/Vonnegut/Namespace.php');

/**
 * Test the basic Vonnegut class
 *
 * @package VonnegutTests
 * @author pete otaqui
 */
class VonnegutTestCase extends PHPUnit_Framework_Testcase
{
    
    public function setUp() {
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
    }
    
    
}

