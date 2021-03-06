<?php
/**
 * UploadBin - Site for sending files.
 *
 * @package uploadbin
 * @author Olle@Johansson.com and Mattias Johansson
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

/**
 * Class for handling page requests.
 */
class EfupAction
{
	/**
	 * An EfupFile object.
	 * @var object
	 */
	private $efup;
	/**
	 * The validated and filtered action argument.
	 * @var string
	 */
	private $action = '';
	/**
	 * The validated and filtered fileName argument.
	 * @var string
	 */
	private $fileName = '';
	/**
	 * The validated and filtered hashed_key argument.
	 * @var string
	 */
	private $hashed_key = '';
	/**
	 * The validated and filtered hashed_name argument.
	 * @var string
	 */
	private $hashed_name = '';
	/**
	 * Username if passed to the script.
	 * @var string
	 */
	private $username = '';
	/**
	 * Password if passed to the script.
	 * @var string
	 */
	private $password = '';
	/**
	 * If the file should be deleted after the first download.
	 * @var string
	 */
	private $firstdownloaderase = '';
	/**
	 * Password assigned to the uploaded file
	 * @var string
	 **/
	private $file_password = '';
	/**
	 * Progressbar widget.
	 * @var object
	 */
	private $fileWidget = null;
	/**
	 * Download filename used to set filename on download.
	 * @var string
	 */
	private $downloadfilename = '';
	/**
	 * Type of requesting client.
	 * @var string
	 */
	private $client = '';
	/**
	 * Email address to send info about uploaded file to.
	 * @var string
	 */
	private $email;
	/**
	 * Allow public listing of file.
	 * @var boolean
	 */
	private $public;

	/**
	 * Constructor which validates the action request variable.
	 *
	 */
	function __construct($dir)
	{
		global $config;

		// Create an EfupFile object.
		$this->efup = new EfupFile($dir);
		
		$this->fileWidget = new UploadProgressMeter();
		$this->fileWidget->maxFileSize = $config->maxuploadsize;
		#$this->fileWidget->enableDebug();
	}

	/**
	 * Validate the input and set the attributes.
	 *
	 * @todo Split into separate validation methods for each action and add presence-metacommands for required fields.
	 */
	function ValidateInput()
	{
		if (isset($_REQUEST['action']))
		{
			$AlnumWS = new Zend_Validate_Alnum(true);
			$filters = array(
				'*' => 'StringTrim',
				'*' => 'StripTags',
				'action' => 'Alpha',
				'name' => 'BaseName',
				'name' => 'StringToLower',
				'name' => 'Alnum',
				'hashed_name' => 'Alnum',
				'hashed_key' => 'Alnum',
				'username' => 'Alnum',
				'client' => 'Alpha',
				'file_password' => 'Alnum',
				'firstdownloaderase' => 'Digits',
				'downloadfilename' => 'BaseName',
				'public' => 'Digits',
			);
			$validators = array(
				'action' => 'Alpha',
				'name' => 'Hex',
				'hashed_name' => 'Hex',
				'hashed_key' => 'Hex',
				'username' => 'Alnum',
				'password' => 'Alnum',
				'client' => array('Alpha', 'allowEmpty' => true),
				'file_password' => array('allowEmpty' => true),
				'firstdownloaderase' => array('Digits', new Zend_Validate_Between(0,1), 'default' => 0),
				'description' => array('allowEmpty' => true, 'default' => ''),
				'downloadfilename' => array('allowEmpty' => true, 'default' => ''),
				'email' => array('EmailAddress', 'allowEmpty' => true, 'default' => ''),
				'public' => array('Digits', new Zend_Validate_Between(0,1), 'default' => '0'),
			);
			$reqs = new Zend_Filter_Input($filters, $validators, $_REQUEST);
			if ($reqs->isValid())
			{
				$this->action = $reqs->action;
				$this->fileName = $reqs->name;
				$this->hashed_name = $reqs->hashed_name;
				$this->hashed_key = $reqs->hashed_key;
				$this->username = $reqs->username;
				$this->password = $reqs->password;
				$this->direct_download = $reqs->password;
				$this->client = strtolower($reqs->client);
				$this->firstdownloaderase = $reqs->firstdownloaderase;
				$this->description = $reqs->description;
				$this->downloadfilename = $reqs->downloadfilename;
				$this->email = $reqs->email;
				$this->public = $reqs->public;
				if( $reqs->file_password != '' )
				{
					$this->file_password = sha1($reqs->file_password);
				}
			} else {
				if ($reqs->hasInvalid()) {
					$errStr .= "Invalid fields: " ;
					foreach($reqs->getInvalid() as $k => $msg)
					{
						$errStr .= "$k<br />\n";
					}
				}

				if ($reqs->hasMissing()) {
					$errStr .= "Missing fields: " ;
					foreach($reqs->getMissing() as $k => $msg)
					{
						$errStr .= "$k<br />\n";
					}
				}

				throw new Exception("Invalid input: $errStr");
			}
		}
	}

	/**
	 * Authenticate the user
	 */
	function Authenticate()
	{
		if ($this->username && $this->password)
		{
		  if (!$this->efup->Authenticate($this->username, $this->password)) {
		    throw new Exception ("Incorrect credentials!");
		  }
		} else {
			$this->efup->SetAuthUser();
		}
	}

	/**
	 * Handle the action request.
	 *
	 */
	function Handle()
	{
		switch ($this->action)
		{
			case "upload": $this->Upload(); break;
			case "direct_download" : $this->Download(); break;
			case "download": $this->Download(); break;	//TODO Show Download Page instead of direct download
			case "list": $this->ListFiles(); break;
			case "listpublic": $this->ListFiles(true); break;
			case "delete": $this->Delete(); break;
			case "getkey": $this->GetKey(); break;
			case "logout": $this->Logout(); break;
			case "login": $this->Login(); break;
			case "contact": $this->ShowPage('contact'); break;
			case "faq": $this->ShowPage('faq'); break;
			case "blog": $this->showPage('blog'); break;
			default:
				$this->ShowPage('index', array(), true, true);
		}
	}

	/**
	 * Upload file(s) via a form.
	 */
	function Upload()
	{
	  $urls = $this->efup->Upload($this->hashed_name, $this->hashed_key, $this->file_password, $this->firstdownloaderase, $this->description, $this->email, $this->public);
		if ($this->client == 'rpc') {
		  echo $urls['downloadurl'];
		} else {
		  $services = $this->ShowPage('services', $urls, false, false, true);
		  $urls = array_merge($urls, array('services' => $services));
		  $message = $this->ShowPage('upload', $urls, false, false, true);
		  #$this->ShowPage('index', array('info' => $message), true, true );
		  echo $message;
		}
	}

	/**
	 * Download a file.
	 */
	function Download()
	{
		$message = $this->efup->Download( $this->fileName, true, $this->file_password, $this->downloadfilename );
		if ($message->code == Message::$ERROR) {

				header('HTTP/1.0 404 Not Found');
				$this->ShowPage('index', array('error' => $message->message), false, true);
		} else if( $message->code != Message::$OK) {
				$this->ShowPage('inputPassword', array('filename' => $this->fileName, 'message' => $message->message ));
		} else {
				$this->ShowPage('index', array('info' => $message->message), false, true);
		}
	}
	 

	/**
	 * Shows files beloning to the current user.
	 * 
	 * @param boolean $public Show public files
	 */
	function ListFiles($public = false)
	{
		$files = $this->efup->ListFiles($public);
		$fc = count($files);
		for ($i = 0; $i < $fc; $i++) {
		  $files[$i]['services'] = $this->ShowPage("services", $files[$i], false, false, true);
		}
		$this->ShowPage("listFiles", array('public' => $public, 'files' => $files));
	}

	/**
	 * Delete a file with the given hash.
	 */
	function Delete()
	{
		$message = $this->efup->Delete( $this->fileName, true );
		$this->ShowPage('index', array('info' => $message->message), false, true);
	}

	/**
	 * Creates a new form key and prints it.
	 */
	function GetKey()
	{
		print $this->efup->GenerateFormKey();
	}

	/**
	 * Log in user.
	 */
	function Login()
	{
		if ($this->username && $this->password)
		{
			$this->efup->Authenticate($this->username, $this->password);
		} else {
			throw new Exception("You must enter both username and password.");
		}
	}

	/**
	 * Logs out the current user.
	 * @uses Zend_Auth
	 */
	function Logout()
	{
		Zend_Auth::getInstance()->clearIdentity();
	}

	/**
	 * Show a page.
	 *
	 * @param string Name of page to load.
	 * @param array Variables to add to the local scope.
	 * @param boolean If true the page won't be allowed to be cached.
	 * @param boolean Whether to generate a form key or not.
	 * @param boolean Return output instead of printing?
	 * @return mixed If $returnoutput is true the output is returned, otherwise a boolean stating success.
	 * @global object Used to make the configuration available to the pages.
	 */
	function ShowPage($name, $vars=array(), $nocache=false, $generateFormKey=false, $returnoutput=false)
	{
		global $config;

		// Show the form.
		$page = "./pages/$name.php";

		// generate form key if requested
		$formkey = $this->efup->GenerateFormKey();
		$vars["formKey"] = $formkey;

		if (file_exists($page))
		{
			// Allow cacheing of page?
			if ($nocache && !$returnoutput)
			{
				header("Pragma: no-cache");
				header("Cache-Control: no-store no-cache, must-revalidate"); // HTTP/1.1
				header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
			}

			// Load the variables into the local scope.
			extract($vars);
			//extract($config);

			// Include header, inclusive upload box
			// Include the page
			if ($returnoutput)
			{
				ob_start();
				include($page);
				$msg = ob_get_contents();
				ob_end_clean();
				return $msg;
			} else {
				include($page);
			}

			// Include footer

			return true;
		} else {
			return false;
		}
	}

}
