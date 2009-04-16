<div id='infoDiv'>
   <?php echo $this->fileWidget->finalStatus(); ?>
   <h1>URL to download this file:</h1>
   <input type="text" name="downloadurl" size="60" value="<?php echo $downloadurl; ?>" onfocus="this.select();" />

   <h1>URL to delete this file:</h1>
   <input type="text" name="deleteurl" size="90" value="<?php echo $deleteurl; ?>" onfocus="this.select();" />

   <h1>Share File:</h1>
   <?php echo $services; ?>
</div>
