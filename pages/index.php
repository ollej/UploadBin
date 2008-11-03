<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" id="uploadbin">
	<head>
		<title>UploadBin</title>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta name="description" content="A free online service to upload and share any file quick and easy. Each uploaded file receives a unique URL which is used to download it by anyone who knows it. Unlimited online web based hard drive solution." />
		<script language="Javascript" type="text/javascript" src="includes/sha256.js"></script>
		<script language="Javascript" type="text/javascript" src="includes/prototype.js"></script>
		<script language="Javascript" type="text/javascript" src="includes/scriptaculous/scriptaculous.js?load=effects"></script>
		<script language="Javascript" type="text/javascript" src="includes/easyfupfuncs.js"></script>
		<script type='text/javascript' src='progressbar.php?client=main,request,httpclient,dispatcher,json,util'></script>
		<script type='text/javascript' src='progressbar.php?stub=UploadProgressMeterStatus'></script>
		<?php echo $this->fileWidget->renderIncludeJs(); ?>
		<link rel="stylesheet" type="text/css" href="css/easyfup.css" />
		<link rel="stylesheet" type="text/css" href="css/progressbar.css" />
	</head>
	<body>
		<div id="uploadDiv">
			<h1><a href="index"><img src="uploadbin.png" id='logo' alt="UploadBin" title="UploadBin" /></a></h1>
			<div id="uploadFormDiv">
			<form id="uploadform" name="uploadform" action="upload" method="post" enctype="multipart/form-data" accept-charset="utf-8" <?php echo $this->fileWidget->renderFormExtra(); ?>>
				<?php echo $this->fileWidget->renderHidden(); ?>
				<input type="hidden" name="action" value="upload" />
				<input type="hidden" name="hashed_name" value="" />
				<input type="hidden" name="hashed_key" value="" />
				<input type="hidden" name="key" value="<?php if (isset($formkey) ) echo $formkey; ?>" />
				<?php echo $this->fileWidget->render(); ?>
				
				<br />
				<a href="javascript:Effect.toggle('advancedDiv', 'blind');">Extra Options</a>
				<div class="centerDiv">
					<div id="advancedDiv">
					<h2>File description</h2>
					<textarea name="description" cols="20" rows="3" class="formclass"></textarea>

					<h2>Password to download file:</h2>
					<input type="password" name="file_password" class="formclass" />

					<h2>Erase file after first download?</h2>
					<input type="radio" name="firstdownloaderase" value="1" /> Yes
					<input type="radio" name="firstdownloaderase" value="0" checked="checked" /> No
					</div>
				</div>

				<div id="progressbarDiv">
				<?php echo $this->fileWidget->renderProgressBar(); ?>
				</div>
				<?php $this->fileWidget->enableDebug(); ?>
				
				<?php if(isset($warning)) { ?>
				<div id='warningDiv'>
					<?php
						echo $warning;
					?>
				</div> <!-- Warning Div -->
				<?php }?>
				<?php if(isset($error)) { ?>
				<div id='errorDiv'>
					<?php
							echo $error;
					?>
				</div> <!-- Error Div -->
				<?php }?>
				<p><input type="submit" value="Continue &#8594;" />
				<img src="images/loading.gif" border="0" alt="Loading" id="loadingimage" /></p>
				<?php if(isset($info)) { ?>
				<div id='infoDiv'>
					<?php
							echo $info;
					?>
				</div> <!-- Info Div -->
				<?php }?>
			</form>
			</div> <!-- Upload Form -->
		</div> <!-- UploadDiv -->
		<div id="help">
			<p>Send your files with UploadBin for free<br />
			1. Select your file and upload it<br />
			2. Receive download link and share it with friends and family</p>
		</div>
		<hr />
		<div id="menuDiv"><a href="javascript:loadContent('list');">List your files</a> | <a href="javascript:loadContent('faq');">FAQ / Rules</a> | <a href="javascript:loadContent('contact');">Contact</a></div>

		<div id='content'>

		</div>
		<p />
		<div id="footerDiv">
			<div id="copyrightDiv">&copy; Copyright 2008 Johansson Corp.</div>
		</div>
	</body>
</html>
