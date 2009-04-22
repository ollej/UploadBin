<?php

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
	function Upload($hashed_name, $hashed_key, $file_password, $firstdownloaderase, $descr, $email, $public)
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
			$fileoptions = $this->_SaveFile($file, $file_password, $firstdownloaderase, $descr, $public);

			// Read the options for the file.
			$filename = $fileoptions['filename'];
			$deletehash = $fileoptions['deletehash'];

			// Everything is OK, add to the list of files.
			$this->files[$filename] = $file;

			// Setup file information
			$downloadfilename = $filename . '/' . urlencode($file->getProp('real'));
			$fileinfo = array(
					  'downloadurl' => $config->siteurl . $downloadfilename,
					  'deleteurl' => $config->siteurl . $deletehash,
					  'downloadurl_enc' => urlencode($config->siteurl . $downloadfilename),
					  'filename_enc' => urlencode($file->getProp('real')),
					  'filename' => $filename
					  );

			// Check if an info email should be sent about the file.
			if (!empty($email)) {
			  $this->sendEmail($email, $fileinfo);
			}

			// Should print a nice table of uploaded files instead.
			if ($this->client == "rpc")
			{
				echo $config->siteurl . $downloadfilename;
			} else {
			  return $fileinfo;
			}

		}
	}

		function sendEmail($email, $fileinfo)
		{
		  global $config, $logger;
		  $body = "You have been sent a file via Uploadbin.net:\n\nYou can download it from this address:\n" . $fileinfo['downloadurl'];
		  $logger->info('Sending email to: ' . $email . ' body: ' . $body);
		  $mail = new Zend_Mail();
		  $mail->setBodyText($body);
		  $mail->setFrom($config->admin->email, $config->admin->name);
		  $mail->addTo($email);
		  $mail->setSubject('Uploadbin.net - Uploaded file: ' . $fileinfo['filename']);
		  $mail->send();
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
		$filename = $this->_LoadFile($hash, false);
		if (!is_string($filename) && $filename->code == Message::$ERROR) {
		  return $filename;
		}
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

		return new Message(Message::$OK, "File {$file->filename} deleted.");
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
	function _SaveFile(&$file, $file_password, $firstdownloaderase, $descr, $public)
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
			'public'      => (int) $public,
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
	        if (!empty($this->files) && isset($this->files[$hash]))
		{
			return $this->files[$hash];
		} else {
			throw new Exception("No such file: $hash");
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
	 * @param boolean $public Whether to show public files or not.
	 */
	function ListFiles($public = false)
	{
		global $config;

		$select = $this->_db->select();
		$select->from(array('f' => $this->_tbl_files));
		if (!$public && isset($_COOKIE[$config->sitename]))
		{
		  foreach ($_COOKIE[$config->sitename] as $name => $value)
		    {
		      $select->orWhere('hashname = ? AND public = 0', $value);
		    }
		} else if ($public) {
		  $select->orWhere('public = ?', $public);
		} else {
		  $select->reset();
		  return array();
		}
		$stmt = $this->_db->query($select);

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
