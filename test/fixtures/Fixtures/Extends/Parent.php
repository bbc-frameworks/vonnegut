<?
/**
 * Parent class for Extension
 * 
 * @package fixtures
 */
class Fixtures_Extends_Parent extends Fixtures_Extends
{
   
   public function overwriteMe() {
       
   }
   
   /**
    * Method to be overwritten with docs in original.
    * 
    * @param array $arg1
    * @return void
    * @author pete otaqui
    **/
   public function overwriteMeWithDocs ($arg1)
   {
       
   }
   
   final public function dontOverwriteMe() {
       
   }
    
}