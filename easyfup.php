<?php
/**
 * UploadBin - Site for sending files.
 *
 * @package uploadbin
 * @author Olle@Johansson.com and Mattias Johansson
 * @uses HTTP/Upload.php
 * @uses HTTP/Download.php
 * @uses Zend/Filter/Input.php
 * @uses Zend/Db.php
 * @uses Zend/Config/Xml.php
 * @uses Zend/Service/Akismet.php
 * @uses Zend/Auth.php
 * @uses Zend/Auth/Adapter/DbTable.php
 */

/*
The MIT License

Copyright (c) 2010 Olle Johansson

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
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
require_once 'Zend/Mail.php';
require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Firebug.php';
require_once 'Zend/Log/Writer/Stream.php';
require_once 'Zend/Log.php';
require_once './includes/size_readable.php';
require_once './includes/UploadProgressMeter.class.php';

/**
 * Include Easyfup classes.
 */
require_once './classes/EfupFile.php';
require_once './classes/EfupAction.php';
require_once './classes/Message.php';
require_once './classes/VirusChecker.php';


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

if ($config->debug == "1") {
  $logger = new Zend_Log();
  $writer1 = new Zend_Log_Writer_Firebug();
  $logger->addWriter($writer1);
  $writer2 = new Zend_Log_Writer_Stream('data/error.log');
  $logger->addWriter($writer2);
}

// Handle the request.
try {
	$efupaction = new EfupAction($config->uploaddir);
	$efupaction->ValidateInput();
	$efupaction->Authenticate();
	$efupaction->Handle();

} catch (Exception $error) {
	// include index page
	// TODO should be dynamic caller/referer
  if (!empty($logger)) {
      $logger->err($error);
  }
  if ($efupaction) {
    $efupaction->showPage('index', array('error' => $error->getMessage()), true, true, false);
  }
}
