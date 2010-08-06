<?
/**
 * This file contains some globals
 * 
 * @author pete otaqui
 * @version $Id$
 * @copyright bbc.co.uk, 30 June, 2010
 * @package standalone
 **/

/**
 * A constant in the global scope
 */
define('GLOBAL_CONSTANT', 12345);

/**
 * A variable in the global scope
 */
$globalVariable = 23456;

/**
 * A function in the global scope
 *
 * @return string "global"
 */
function globalFunction($dolittle) {
    return "global";
}

