<div id='infoDiv'>
   <?php echo $this->fileWidget->finalStatus(); ?>
   <h1>URL to download this file:</h1>
   <input type="text" name="downloadurl" size="60" value="<?php echo $downloadurl; ?>" onfocus="this.select();" />

   <h1>URL to delete this file:</h1>
   <input type="text" name="deleteurl" size="90" value="<?php echo $deleteurl; ?>" onfocus="this.select();" />

   <h1>Share File:</h1>
   <a href="http://www.facebook.com/share.php?u=<?php echo $downloadurl_enc; ?>"><img src="images/share/facebook.png" alt="Share on Facebook" title="Share on Facebook" /></a>
   <a href="http://twitter.com/home?status=<?php echo $downloadurl_enc; ?>"><img src="images/share/twitter.png" alt="Share on Twitter" title="Share on Twitter" /></a>
   <a href="http://tinyurl.com/create.php?url=<?php echo $downloadurl_enc; ?>"><img src="images/share/tinyurl.png" alt="Create Tiny URL" title="Create Tiny URL" /></a>
</div>
