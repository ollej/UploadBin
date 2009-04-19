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

/**
 * Class for handling files.
 * @package easyfup
 */
class EfupFile
{
	/**
	 * List of loaded files.
	 * @var array
	 */
	private $files;
	/**
	 * Path to the directory where all uploaded files will be saved.
	 * @var string
	 */
	public $dir;
	/**
	 * A Zend Db connection instance.
	 * @var object
	 */
	private $_db;
	/**
	 * Authentication object.
	 * @var object
	 */
	private $_auth;
	/**
	 * Identity of a logged in user.
	 * @var object
	 */
	public $identity;
	/**
	 * Name of the files table.
	 * @var string
	 */
	private $_tbl_files;
	/**
	 * Name of the formkeys table.
	 * @var string
	 */
	private $_tbl_keys;
	/**
	 * Client type.
	 * @var string
	 */
	public $client;



	/**
	 * Constructor which initializes some values.
	 *
	 * @param string $dir Server path to upload directory.
	 * @todo Use realpath validator on the dir.
	 * @uses Zend_Db
	 * @global object Used to read configuration values.
	 */
	function __construct ($dir)
	{
		global $config;

		// Connect to the database.
		$this->files = array();
		$this->_db =  Zend_Db::factory($config->database->type, array(
			'host'     => $config->database->host,
			'username' => $config->database->username,
			'password' => $config->database->password,
			'dbname'   => $config->database->name,
			'profiler' => true
		));

		// Set name of files table.
		$this->_tbl_files = $config->database->prefix . $config->database->table->files;

		// Set name of key table
		$this->_tbl_keys = $config->database->prefix . $config->database->table->formkeys;

		// Set the file directory.
		$this->dir = $dir;

		// Read extensions to deny from the configuration.
		if (trim($config->denyextensions))
		{
			$this->denyextensions = $this->ExplodeTrim($config->denyextensions);
		} else {
			$this->denyextensions = array();
		}

		// Make sure the file directory exists and is writeable.
		if (!file_exists($this->dir))
		{
			throw new Exception("Upload directory doesn't exist.");
		} else if (!is_writeable($this->dir)) {
			throw new Exception("Upload directory is not writeable.");
		}

		// Set up authentication
		$this->_auth = new Zend_Auth_Adapter_DbTable($this->_db, 'users', 'username', 'password', 'sha1(?)');
	}

	/**
	 * Save an uploaded file.
	 *
	 * Mainly uses example code from the PEAR HTTP Upload package.
	 *
	 * @todo Handle password to file if necessary.
	 * @todo Refactor all formkey handling into a separate class.
	 *
	 * @param string $hashed_name Hashed version of the filename of the uploaed file.
	 * @param string $hashed_key Hashed version of the form key.
	 * @param boolean $firstdownloaderase True if file should be erased after first download.
	 * @uses HTTP/Upload.php
	 * @global object Used to read configuration values.
	 */
	function Upload($hashed_name, $hashed_key, $file_password, $firstdownloaderase, $descr)
	{
		global $config;

		// Delete all old keys.
		$where_del = "added < DATE_SUB(CURDATE(), INTERVAL 1 HOUR)";
		$this->_db->delete($this->_tbl_keys, $where_del);

		// Try to find the calculated formkey in the db if user isn't logged in.
		if (!$this->isLoggedIn())
		{
			if (!$this->CheckFormKey($hashed_key))
			{
				throw new Exception("Not a valid form key: $hashed_key");
			}
		}

		// Check if this is considered spam.
		// When we have description, add in the argument array as comment_content
		if ($this->SpamCheck())
		{
			throw new Exception("You seem to be spamming us. Shame on you!");
		}

		// Use HTTP_Upload to read all files.
		$upload = new HTTP_Upload("en");
		$files = $upload->getFiles();

		// Set execution time and max upload size.
		ini_set('max_execution_time', $config->maxexecutiontime);
		ini_set('max_upload_size', $config->maxuploadsize);

		// Parse through the files and save them to the disk.
		foreach ($files as $file)
		{
			// Set denied extensions.
			$file->setValidExtensions($this->denyextensions, "deny");

			// Check if there is an error.
			if ($file->isError()) {
				throw new Exception($file->errorMsg());
			}

			// Make sure the upload is valid.
			if (!$file->isValid())
			{
				if ($file->upload['error'])
				{
					throw new Exception($file->getMessage());
				} else {
					throw new Exception("Unknown file upload error.");
				}
			}

			// Make sure the real filename has been hashed.
			// This is to make sure the client has run sha256 on the filename and
			// thus is hopefully a real user using a real browser.
			// Only needed if user isn't logged in.
			if (!$this->isLoggedIn())
			{
				if ($this->getHash('sha256', '', $file->getProp('real')) != $hashed_name)
				{
					throw new Exception("Hashed filenames doesn't match: real: ".$file->getProp('real').", sha256: ".hash('sha256', $file->getProp('real')).", hashed_name: $hashed_name");
				}
			}

			// Save the file.
			$fileoptions = $this->_SaveFile($file, $file_password, $firstdownloaderase, $descr);

			// Read the options for the file.
			$filename = $fileoptions['filename'];
			$deletehash = $fileoptions['deletehash'];

			// Everything is OK, add to the list of files.
			$this->files[$filename] = $file;

			// Should print a nice table of uploaded files instead.
			$downloadfilename = $filename . '/' . urlencode($file->getProp('real'));
			if ($this->client == "rpc")
			{
				echo $config->siteurl . $downloadfilename;
			} else {
				return array(
					'downloadurl' => $config->siteurl . $downloadfilename,
					'deleteurl' => $config->siteurl . $deletehash,
					'downloadurl_enc' => urlencode($config->siteurl . $downloadfilename),
					'filename_enc' => urlencode($file->getProp('real'))
				);
			}

		}
	}

	/**
	* Downloads a file with the supplied file name.
	*
	* Throws an exception if the file is locked.
	*
	* @param string $hash Hashname of the file to download.
	* @param string $password Password of the file to download.
	* @param message $message Status of the download
	* @uses HTTP/Download.php
	* @global object Configuration options.
	*/
	function Download($hash, $bool, $password, $downloadfilename = NULL)
	{
		global $config;

		// Load the file.
		$err = $this->_LoadFile($hash, false);
		if (!is_string($err) && $err->code == Message::$ERROR) {
		  return $err;
		}
		$file = $this->getFile($hash);

		// Make sure the file isn't locked.
		if ($this->isLocked($hash))
		{
			throw new Exception("This file is locked and can't be downloaded.");
		}

		// Check for password.
		$checkPassword = $this->checkPassword($hash, $password);

		if ($checkPassword->code != Message::$OK )
			return $checkPassword;


		// Set execution time and max upload size.
		ini_set('max_execution_time', $config->maxexecutiontime);

		// Send the file.
		$dl = new HTTP_Download();
		$dl->setFile($this->dir . $hash);
		$filename = ($downloadfilename != '') ? $downloadfilename : $file->filename;
		if (dirname($file->mime_type) == 'image') {
			$disposition = HTTP_DOWNLOAD_INLINE;
		} else {
			$disposition = HTTP_DOWNLOAD_ATTACHMENT;
		}
		$dl->setContentDisposition($disposition, $filename);
		$dl->setContentType($file->mime_type);
		$error = $dl->send();

		if (PEAR::isError($error))
		{
			throw new Exception($error->getMessage());
		}

		// Count this traffic usage
		$this->CountTraffic($hash);

		// Delete file if necessary
		if ($file->firstdownloaderase)
		{
			$this->Delete($hash);
		}

		$message = new Message();
		$message->code = Message::$OK;
		$message->message = "Please wait while file is being downloaded";
		return $message;

	}

	function Show()
	{

	}

	/**
	 * Delete a file from the database.
	 *
	 * If the file is loaded in $this->files it is removed.
	 *
	 * @param string $hash Hashname of the file to delete.
	 * @param boolean $hashtype If true the hash is a deletehash instead of a filename hash.
	 * @global object Used to read configuration values.
	 */
	function Delete($hash, $hashtype=false)
	{
		global $config;

		// Load the file information.
		$filename = $this->_LoadFile($hash, $hashtype);
		if (!$filename)
		{
			throw new Exception("File not found.");
		}
		$file = $this->getFile($filename);

		// Delete the file.
		$where = "hashname = '$filename'";
		$n = $this->_db->delete($this->_tbl_files, $where);

		// Remove the file from the object list.
		if (isset($this->files[$filename]))
		{
			unset($this->files[$filename]);
		}

		// Remove the cookie for this file.
		if (!setcookie( $config->sitename.'['.$filename.']', $filename, time() - $config->cookielifetime ))
		{
			throw new Exception("Couldn't delete cookie for the file.");
		}

		// Delete the file from disk.
		if (file_exists($this->dir . $filename))
		{
			unlink($this->dir . $filename);
		}

		print "File {$file->filename} deleted.";
	}

	/**
	 * Update the database with the traffic used up by this download.
	 *
	 * Also updates the download counter and the last_download time.
	 *
	 * @param string $hash Hashname of the file to update.
	 * @global object Used to read configuration values.
	 */
	function CountTraffic($hash)
	{
		global $config;

		if (!isset($this->files[$hash]))
		{
			throw new Exception("Tried to count traffic on unloaded file.");
		}

		$file =& $this->files[$hash];
		$data = array(
			'traffic_used' => $file->size + $file->traffic_used,
			'download_count' => $file->download_count + 1,
			'last_download' => date('Y-m-d H:i:s'),
		);
		$where = "hashname = '$hash'";
		$n = $this->_db->update($this->_tbl_files, $data, $where);
	}
	
	/**
	 * Locks a file and prevents it from being downloaed.
	 *
	 * @param string $hash Hashname of file to lock.
	 * @global object Used to read configuration values.
	 */
	function Lock($hash)
	{
		global $config;

		$data = array(
			'locked' => 1,
		);
		$where = "hashname = '$hash'";
		$n = $this->_db->update($this->_tbl_files, $data, $where);
	}

	/**
	 * Unlocks a file to make downloadable again.
	 *
	 * @param string $hash Hashname of file to unlock.
	 * @global object Used to read configuration values.
	 */
	function Unlock($hash)
	{
		global $config;

		$data = array(
			'locked' => 0,
		);
		$where = "hashname = '$hash'";
		$n = $this->_db->update($this->_tbl_files, $data, $where);
	}

	/**
	 * Set password for a file.
	 *
	 * @param string $hash Hash for the file.
	 * @param string $password Password for the file.
	 */
	function SetPassword($hash, $password)
	{

	}

	/**
	 * Loads a file from disk and reads extra information from the db.
	 *
	 * If the hash is already loaded, nothing is done.
	 *
	 * @param string $hash Hash name of the file to load.
	 * @param boolean $hashtype True if the hash is a deletehash.
	 * @return string Hashname of the file loaded.
	 * @global object Used to read configuration values.
	 */
	function _LoadFile($hash, $hashtype=false)
	{
		global $config;

		// Whether the hash is a deletehash or a hashname.
		if ($hashtype)
		{
			$hashfield = 'deletehash';
		} else {
			$hashfield = 'hashname';
		}

			if (!isset($this->files[$hash]))
			{
				$select = $this->_db->select()
					->from(array('f' => $this->_tbl_files))
					->where("$hashfield = ?", $hash);
				$stmt = $this->_db->query($select);
				$file = $stmt->fetchObject();
				if (!empty($file)) {
				  $this->files[$file->hashname] = $file;
				} else {
				  return new Message(Message::$ERROR, "File not found: $hash");
				  #throw new Exception("File not found: $hash");
				}
			}

		return $file->hashname;
	}

	/**
	 * Save a file to disk and save info about it in the db.
	 *
	 * @param object $file HTTP_Upload file object.
	 * @global object Used to read configuration values.
	 */
	function _SaveFile(&$file, $file_password, $firstdownloaderase, $descr)
	{

		global $config;

		// Create a hash for this file.
		$hash = $this->getHash($config->hashtype);

		// Set a filename and move the file to the directory.
		$file->setName($hash);
		$dest_name = $file->moveTo($this->dir);

		// Make sure the upload worked.
		if (PEAR::isError($dest_name))
		{
			throw new Exception($dest_name->getMessage());
		}

		// Save the hash for this file in user cookie.
		if (!setcookie( $config->sitename.'['.$hash.']', $hash, time() + $config->cookielifetime ))
		{
			throw new Exception("Couldn't save cookie for the file.");
		}

		// Create extra options for this file.
		$deletehash = $this->getHash('sha256', 'delete', $dest_name, true);

		// Create query
		$data = array(
			'filename'    => $file->getProp('real'),
			'hashname'    => $dest_name,
			'extension'   => $file->getProp('ext'),
			'mime_type'   => $file->getProp('type'),
			'size'	      => $file->getProp('size'),
			'password'    => $file_password,
			'locked'      => 0,
			'uploaded'    => date('Y-m-d H:i:s'),
			'firstdownloaderase' => $firstdownloaderase,
			'deletehash'  => $deletehash,
			'description' => $descr,
		);

		// Save information about the file in the database.
		$this->_db->insert($this->_tbl_files, $data);

		return array('filename' => $hash, 'deletehash' => $deletehash);
	}

	/**
	 * Returns a unique hash name.
	 *
	 * $type can be any of the algorithms returned by hash_algos()
	 * or uniqid (which uses uniqid() PHP function)
	 * or uuid (which uses the UUID() MySQL function).
	 *
	 * uuid uses A MySQL DB connection to get the hash.
	 *
	 * @param string What type of hash to return.
	 * @param string A seed to add to use with the hash, if possible.
	 * @return string Unique hash name.
	 * @global object Used to read configuration values.
	 */
	function getHash($type = 'sha1', $seed='', $key='', $random=false)
	{
		global $config;
		$hashstr = $seed . ($key) ? $key : mt_rand() . getenv('REMOTE_ADDR');
		if ($random)
		{
			$hashstr .= mt_rand();
		}

		if ($type == 'uniqid')
		{
			$hash = uniqid($hashstr, true);
		} else if ($type == "uuid" && (strtolower($config->database->type) == "pdo_mysql")) {
			// Reads uuid from MySQL.
			$select = $this->_db->select()->from('', array("id" => new Zend_Db_Expr("UUID()")));
			$stmt = $this->_db->query($select);
			$uuid = $stmt->fetchObject();
			$hash = str_replace('-', '', $uuid->id);
		} else if (in_array($type, hash_algos())) {
			$hash = hash($type, $hashstr);
		} else {
			throw new Exception("Unknown hash type.");
		}

		return $hash;
	}

	/**
	 * Returns the file object of the given hash if it is loaded.
	 *
	 * Throws an exception if $hash isn't loaded.
	 *
	 * @param string $hash Hash name of a file.
	 * @return object Object with information about the file.
	 */
	function getFile($hash)
	{
		if (isset($this->files[$hash]))
		{
			return $this->files[$hash];
		} else {
			throw new Exception("File isn't loaded.");
		}
	}

	/**
	 * Checks if a file with the given hash is locked.
	 *
	 * Throws an exception if $hash isn't loaded.
	 *
	 * @param string $hash Hashname of file to check.
	 * @return boolean true if the file is locked.
	 */
	function isLocked($hash)
	{
		if (!isset($this->files[$hash]))
		{
			throw new Exception("Tried to run isLocked on unloaded file.");
		}

		if ($this->files[$hash]->locked == 1)
		{
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks if a file with the given is password protected
	 *
	 * Throws an exception if $hash isn't loaded.
	 *
	 * @param string $hash Hashname of file to check.
	 * @return true if password is either not set or correct
	 */
	function checkPassword($hash, $password)
	{
		$message = new Message();

		// Return true if either password is not set or password is correct
		if (isset( $this->files[$hash]->password ) && $this->files[$hash]->password!="" &&  $password=="")
		{
			$message->code = Message::$PASSWORD_REQUIRED;
			$message->message = "Please enter password";
		} elseif ( $this->files[$hash]->password!=$password ) {
			$message->message = "Invalid password";
			$message->code = Message::$INVALID_PASSWORD;
		} else {
			$message->code = Message::$OK;
		}
		return $message;
	}


	/**
	 * Lists files uploaded based on hash in cookie.
	 *
	 * @global object Used to read configuration values.
	 */
	function ListFiles()
	{
		global $config;

		if (isset($_COOKIE[$config->sitename]))
		{
			$select = $this->_db->select();
			$select->from(array('f' => $this->_tbl_files));
			foreach ($_COOKIE[$config->sitename] as $name => $value)
			{
				$select->orWhere('hashname = ?', $value);
			}
			$stmt = $this->_db->query($select);
		}

		if (empty($stmt)) return array();

		$maxlen = (int) $config->maxdescinlist;
		$files = array();
		while ($row = $stmt->fetch())
		{
			$descdec = html_entity_decode($row['description']);
			if (strlen($descdec) > $maxlen)
			{
				$shortdesc = htmlentities(substr($descdec, 0, $maxlen) . "...");
			} else {
				$shortdesc = $row['description'];
			}
			$row['size_readable'] = size_readable($row['size'], null, '%01.2f %s', false);
			$row['description_short'] = $shortdesc;
			$row['description_enc'] = urlencode($descdec);
			$row['downloadurl'] = $config->siteurl . $row['hashname'] . '/' . $row['filename'];
			$row['downloadurl_enc'] = urlencode($row['downloadurl']);
			$row['deleteurl'] = $config->siteurl . 'delete/' . $row['deletehash'];
			$row['filename_enc'] = urlencode($row['filename']);
			
			$files[] = $row;

		}

		return $files;
	}

	/**
	 * Checks if the uploaded data is considered spam.
	 *
	 * @param array Extra fields added to the Akismet service.
	 * @return boolean Returns true if the submission is considered spam.
	 * @uses Zend_Service_Akismet()
	 * @global object Used to read configuration values.
	 */
	function SpamCheck($extradata = null)
	{
		global $config;

		// Instantiate with the API key and a URL to the application or resource being
		// used
		$akismet = new Zend_Service_Akismet($config->akismetkey, $config->siteurl);

		// Check if the upload is considered spam by Akismet
		$data = array(
			'user_ip' => getenv("REMOTE_ADDR"),
			'user_agent' => getenv("HTTP_USER_AGENT"),
			'referrer' => getenv("HTTP_REFERER"),
			'comment_author' => '',
			'comment_email' => '',
		);
		if (isset($extradata) && is_array($extradata))
		{
			$data = array_merge($data, $extradata);
		}
		return $akismet->isSpam($data);
	}

	/**
	 * Explodes a string into array on $splitchar and trims each element.
	 *
	 * @param string $string String to explode into array.
	 * @param string $splitchar Charactoer to explode on.
	 * @return array A list of trimmed elements.
	 */
	function ExplodeTrim($string, $splitchar=",")
	{
		$arr = explode($splitchar, $string);
		$count = count($arr);
		for ($i = 0; $i < $count; $i++)
		{
			$arr[$i] = trim($arr[$i]);
		}
		return $arr;
	}

	/**
	 *
	 */

	/**
	 * Authenticate user with credentials.
	 *
	 * @param string $username Username
	 * @param string $password Password
	 * @return boolean True if user is authenticated.
	 */
	function Authenticate($username, $password)
	{
		// Set the input credential values (e.g., from a login form)
		$this->_auth->setIdentity($username)
        			->setCredential($password);

		// Perform the authentication query, saving the result
		$auth = Zend_Auth::getInstance();
		$result = $auth->authenticate($this->_auth);

		if ($result->isValid())
		{
		  // Save the uesr identity.
		  $this->SetAuthUser();

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Save the user identity.
	 *
	 */
	function SetAuthUser()
	{
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity())
		{
    		// Identity exists; get it
			$this->identity = $auth->getIdentity();
		} else {
			unset($this->identity);
		}
	}

	/**
	 * Logs out the current user.
	 */
	function Logout()
	{
		$this->_auth->clearIdentity();
		unset($this->identity);
	}

	/**
	 * Checks if the user is logged in.
	 * @return boolean True if the user is logged in.
	 */
	function isLoggedIn()
	{
		if (isset( $this->identity ))
		{
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Generates a new form key and saves it in the db.
	 *
	 * @return string The generated form key.
	 * @global object Used to read configuration values.
	 */
	function GenerateFormKey()
	{
		global $config;

		$formkey = $this->getHash('uniqid');

		// Create query
		$data = array(
			'formkey' => hash('sha256', $formkey),
			'ip'  => getenv("REMOTE_ADDR"),
		);

		// Save information about the formkey in the database.
		$this->_db->insert($this->_tbl_keys, $data);

		return $formkey;
	}

	/**
	 * Check if a valid form key exists.
	 * @return boolean True if a valid form key existed.
	 */
	function CheckFormKey($hashed_key)
	{
	  if (!$hashed_key) return false;

		$select = $this->_db->select()
			->from(array('k' => $this->_tbl_keys))
			->where('formkey = ?', $hashed_key)
			->where('ip = ?', getenv("REMOTE_ADDR"));
		$stmt = $this->_db->query($select);
		$formkey = $stmt->fetchAll();
		$select->reset();

		// Delete used key.
		$where_del = ARRAY(
			"formkey = '$hashed_key'",
			"ip = '" . getenv("REMOTE_ADDR") . "'",
		);
		$this->_db->delete($this->_tbl_keys, $where_del);

		if ($formkey)
		{
			return true;
		} else {
			return false;
		}

	}

}

/**
 * Class for handling page requests.
 * @package easyfup
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
	 * Constructor which validates the action request variable.
	 *
	 */
	function __construct($dir)
	{
		// Create an EfupFile object.
		$this->efup = new EfupFile($dir);
		
		$this->fileWidget = new UploadProgressMeter();
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
				'password' => 'Alnum',
				'client' => 'Alpha',
				'file_password' => 'Alnum',
				'firstdownloaderase' => 'Digits',
				'downloadfilename' => 'BaseName',
			);
			$validators = array(
				'action' => 'Alpha',
				'name' => 'Hex',
				'hashed_name' => 'Hex',
				'hashed_key' => 'Hex',
				'username' => 'Alnum',
				'password' => 'Alnum',
				'client' => 'Alpha',
				'file_password' => array('Alnum', 'allowEmpty' => true),
				'firstdownloaderase' => array('Digits', new Zend_Validate_Between(0,1), 'default' => 0),
				'description' => array('allowEmpty' => true, 'default' => ''),
				'downloadfilename' => array('allowEmpty' => true, 'default' => ''),
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
				$this->direct_dowload = $reqs->password;
				$this->efup->client = strtolower($reqs->client);
				$this->firstdownloaderase = $reqs->firstdownloaderase;
				$this->description = $reqs->description;
				$this->downloadfilename = $reqs->downloadfilename;
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
		$urls = $this->efup->Upload($this->hashed_name, $this->hashed_key, $this->file_password, $this->firstdownloaderase, $this->description);
		if ($this->client != 'rpc') {
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
	 */
	function ListFiles()
	{
		$files = $this->efup->ListFiles();
		$fc = count($files);
		for ($i = 0; $i < $fc; $i++) {
		  $files[$i]['services'] = $this->ShowPage("services", $files[$i], false, false, true);
		}
		$this->ShowPage("listFiles", array('files' => $files));
	}

	/**
	 * Delete a file with the given hash.
	 */
	function Delete()
	{
		$this->efup->Delete( $this->fileName, true );
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

class Message
{
	public $message;
	public $code;

	public static $OK = 0;
	public static $ERROR = -1;
	public static $PASSWORD_REQUIRED = 1;
	public static $INVALID_PASSWORD = 2;

	function __construct($code = NULL, $message = NULL) {
	  $this->code = $code;
	  $this->message = $message;
	}
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
  if ($efupaction) {
    $efupaction->showPage('index', array('error' => $error->getMessage()), true, true, false);
  }
}
