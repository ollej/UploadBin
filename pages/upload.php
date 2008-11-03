<div id='infoDiv'>
   <?php echo $this->fileWidget->finalStatus(); ?>
   <p>URL to download this file:<br />
   <input type="text" name="downloadurl" size="60" value="<?php echo $downloadurl; ?>" onfocus="this.select();" /></p>

   <p>URL to delete this file:<br />
   <input type="text" name="deleteurl" size="90" value="<?php echo $deleteurl; ?>" onfocus="this.select();" /></p>
</div>