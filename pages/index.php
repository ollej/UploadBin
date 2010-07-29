<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" id="uploadbin">
	<head>
		<title>UploadBin</title>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<base href="<?php echo $config->siteurl; ?>">
		<meta name="description" content="A free online service to upload and share any file quick and easy. Each uploaded file receives a unique URL which is used to download it by anyone who knows it. Unlimited online web based hard drive solution." />
		<script language="Javascript" type="text/javascript" src="includes/sha256-wbt.js"></script>
		<script language="Javascript" type="text/javascript" src="includes/n11ndata.js"></script>
		<script language="Javascript" type="text/javascript" src="includes/nfc.js"></script>
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
			<h1><a href="index"><img src="images/uploadbin.png" id='logo' alt="UploadBin" title="UploadBin" /></a></h1>
			<div id="uploadFormDiv">
			<form id="uploadform" name="uploadform" action="upload" method="post" enctype="multipart/form-data" accept-charset="utf-8" <?php echo $this->fileWidget->renderFormExtra(); ?>>
				<?php echo $this->fileWidget->renderHidden(); ?>
				<input type="hidden" name="action" value="upload" />
				<input type="hidden" name="hashed_name" value="" />
				<input type="hidden" name="hashed_key" value="" />
				<input type="hidden" name="key" value="<?php if (isset($formkey) ) echo $formkey; ?>" />
				<?php echo $this->fileWidget->render(); ?>
				
				<br />
				<a href="javascript:Effect.toggle('advancedDiv', 'blind', { duration: 0.1 });">Extra Options</a>
				<div class="centerDiv">
					<div id="advancedDiv">
						<h2><label for="description" title="Description of the file that will be listed in the file list.">File description:</label></h2>
						<textarea id="description" name="description" cols="20" rows="3" class="formclass"></textarea>

						<h2><label for="file_password" title="Enter a password that needs to be supplied to be able to download the file.">Password to download file:</label></h2>
						<input type="password" id="file_password" name="file_password" class="formclass" />

						<h2 title="If set to Yes, this file will be deleted once it has been downloaded.">Erase file after first download?</h2>
						<input type="radio" name="firstdownloaderase" id="fdeYes" value="1" />
						<label for="fdeYes">Yes</label>
						<input type="radio" name="firstdownloaderase" id="fdeNo" value="0" checked="checked" />
						<label for="fdeNo">No</label>

						<h2><label for="email" title="If an email address is supplied, an email with the download URL to the file will be sent to the address.">Email file:</label></h2>
						<input type="text" name="email" id="email" value="" class="formclass" />

						<h2><label for="public" title="If set to Yes, this file will be listed publically on the site and can be downloaded by anyone.">Allow public listing?</label></h2>
						<input type="radio" name="public" id="pubYes" value="1" />
						<label for="pubYes">Yes</label>
						<input type="radio" name="public" id="pubNo" value="0" checked="checked" />
						<label for="pubNo">No</label>

					</div> <!-- Advanced Div -->
				</div> <!-- Center Div -->

				<div id="progressbarDiv">
				<?php echo $this->fileWidget->renderProgressBar(); ?>
				</div> <!-- Progressbar Div -->
				<?php $this->fileWidget->enableDebug(); ?>
				
				<?php if (isset($warning)) { ?>
				<div id='warningDiv'>
					<?php echo $warning; ?>
				</div> <!-- Warning Div -->
				<?php }?>
				<?php if (isset($error)) { ?>
				<div id='errorDiv'>
				<h1>Error</h1>
					<p><?php echo $error; ?></p>
				</div> <!-- Error Div -->
				<?php } ?>
				<p><input type="submit" value="Continue &#8594;" />
				<img src="images/loading.gif" border="0" alt="Loading" id="loadingimage" /></p>
				<?php if (isset($info)) { ?>
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
		</div> <!-- Help Div -->
		<hr />
		<div id="menuDiv"><a href="javascript:loadContent('list');">List your files</a> | <a href="javascript:loadContent('listpublic');">List public files</a> | <a href="javascript:loadContent('faq');">FAQ / Rules</a> | <a href="javascript:loadContent('blog');">Blog</a> | <a href="javascript:loadContent('contact');">Contact</a></div>

		<div id='content'>
		<?php if (!empty($content)) { ?>
		    <?php echo $content; ?>
		<?php } ?>
		</div> <!-- Content Div -->
		<div id='debug'></div>

		<div id="footerDiv">
			<div id="copyrightDiv">&copy; Copyright 2009-2010 Johansson Corp.</div>
		</div> <!-- Footer Div -->
	</body>
</html>
