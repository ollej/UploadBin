<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" id="uploadbin">
	<head>
		<title>UploadBin</title>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta name="description" content="A free online service to upload and share any file quick and easy. Each uploaded file receives a unique URL which is used to download it by anyone who knows it. Unlimited online web based hard drive solution." />
		<link rel="stylesheet" type="text/css" href="css/easyfup.css">
	</head>
	<body>
		<form id="password_field" action="easyfup.php" method="post">
			<input type="hidden" value="<?php echo $filename; ?>" name="name" />
			<input type="hidden" value="download" name="action" />
			<p>Password: <input type="password" name="file_password" /></p>
			<p><input type="submit" value="Continue &#8594;" /></p>
		</form>
		<?php
			if( isset( $message ) ) {
				echo "<div id='errorDiv'/>";
				echo $message;
				echo "</div>";
			}
		
		?>
	</body>
</html>