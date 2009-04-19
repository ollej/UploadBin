<?php
/**
 * easyfup - Enkel filuppladdning.
 *
 * @package easyfup
 * @author Olle@Johansson.com and Mattias Johansson
 * @version $Id$
 * @uses HTTP/Upload.php
 * @uses HTTP/Download.php
 * @uses Zend/Filter/Input.php
 * @uses Zend/Db.php
 * @uses Zend/Config/Xml.php
 * @uses Zend/Service/Akismet.php
 * @uses Zend/Auth.php
 * @uses Zend/Auth/Adapter/DbTable.php
 */

// -----------------------------------------------------------------------------
// Start of files to include.

/**
 * Include external classes
 */
require_once "HTTP/Upload.php";
require_once "HTTP/Download.php";
require_once "Zend/Filter/Input.php";
require_once 'Zend/Db.php';
require_once 'Zend/Config/Xml.php';
require_once 'Zend/Service/Akismet.php';
require_once 'Zend/Auth.php';
require_once 'Zend/Auth/Adapter/DbTable.php';
require_once 'Zend/Validate/Between.php';
require_once 'Zend/Validate/Alnum.php';
require_once './includes/size_readable.php';
require_once './includes/UploadProgressMeter.class.php';

/**
 * Include Easyfup classes.
 */
require_once './classes/EfupFile.php';
require_once './classes/EfupAction.php';
require_once './classes/Message.php';


// End of files to include.
// -----------------------------------------------------------------------------

// -----------------------------------------------------------------------------
// Start of global variables.

/**
 * An object containing all the configuration values read from config.xml
 * @global object $config
 * @name $config
 */
$config = new Zend_Config_Xml('config.xml', 'staging');

// End of global variables.
// -----------------------------------------------------------------------------

// Handle the request.
try {
	$efupaction = new EfupAction($config->uploaddir);
	$efupaction->ValidateInput();
	$efupaction->Authenticate();
	$efupaction->Handle();

} catch (Exception $error) {
	// include index page
	// TODO should be dynamic caller/referer
  if ($efupaction) {
    $efupaction->showPage('index', array('error' => $error->getMessage()), true, true, false);
  }
}
