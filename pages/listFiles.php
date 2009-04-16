
			<table width='100%' id="listTable" class="itemlist">
			<tr>
				<th>
					File
				</th>
				<th>
					Description
				</th>
				<th class="numbers">
					Size
				</th>
				<th class="numbers">
					Downloads
				</th>
				<th align="center">
					Services
				</th>
				<th>
					Delete
				</th>
			</tr>
			<?php
				$i = 0;
				while ($row = each($files))
				{
					$rowalt = (++$i % 2) ? "_odd" : "_even";
					echo "<tr class='row$rowalt'>";
 					echo '<td><a href="';
					echo $row[1]['downloadurl'];
					echo '">';
					echo $row[1]['filename'];
					echo '</a>';
					echo '</td>';
					echo '<td>';
					echo $row[1]['description_short'];
					echo '</td>';
					echo '<td class="numbers">';
					echo $row[1]['size_readable'];
					echo '</td><td class="numbers">';
					echo $row[1]['download_count'];
					echo '</td><td align="center">';
					echo $row[1]['services'];
					echo '</td>';
					echo '<td align="center">';
					if (trim($row[1]['deletehash']))
					{
						echo '<a href="' . $row[1]['deleteurl'] . '">';
						echo '<img alt="Delete file" title="Delete file" border="0" src="images/delete.png" />';
						echo '</a>';
					}
					echo '</td>';
					echo '</tr>';
				}
			?>
		</table>
