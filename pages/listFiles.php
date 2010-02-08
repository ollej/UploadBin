
			<table width='100%' id="listTable" class="itemlist">
			<tr>
				<th>File</th>
				<th>Description</th>
				<th class="numbers">Size</th>
				<th class="numbers">Downloads</th>
				<th align="center">Services</th>
				<?php if (!$public): ?>
				<th>Delete</th>
				<?php endif; ?>
			</tr>
			<?php
				$i = 0;
				while (list($k, $row) = each($files))
				{
					extract($row);
					$rowalt = (++$i % 2) ? "_odd" : "_even";
?>
            <tr class='row<?php echo $rowalt; ?>'>
                <td><a href="<?php echo $downloadurl; ?>"><?php echo $filename; ?></a></td>
                <td><span title="<?php echo $description; ?>"><?php echo $description_short; ?></span></td>
                <td class="numbers"><?php echo $size_readable; ?></td>
                <td class="numbers"><?php echo $download_count; ?></td>
                <td align="center"><?php echo $services; ?></td>
                <td align="center">
                <?php if ($deletehash && !$public): ?>
                    <a href="<?php echo $deleteurl; ?>">
                    <img alt="Delete file" title="Delete file" border="0" src="images/delete.png" />
                    </a>
                <?php endif; ?>
                </td>
            </tr>
<?php } ?>
		</table>
