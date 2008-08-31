<?php
/**
 * UploadProgressMeterStatus.class.php - Upload progress Meter backend
 *
 * Copyright (C) 2005  Joshua Eichorn  This program is free software; you can 
 * redistribute it and/or modify it under the terms of the GNU General Public 
 * License as published by the Free Software Foundation; either version 2 of 
 * the License, or (at your option) any later version.  This program is 
 * distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE.  See the GNU General Public License for more details.  
 * You should have received a copy of the GNU General Public License along 
 * with this program; if not, write to the Free Software Foundation, Inc., 
 * 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 * 
 *
 * @author       Joshua Eichorn <josh@bluga.net>
 * @copyright    Joshua Eichorn (c)2005
 * @link         http://bluga.net/projects/upload_progress_meter
 * @version      0.2
 * @filesource
 */
	
/**
 * Get the status of an in progress upload, 
 *
 * Requires the Upload Progress Meter extension
 */
class UploadProgressMeterStatus {
	/**
	 * Get the status of all uploads passed in
	 */
	function getStatus($ids) {
		$ret = array();
		foreach($ids as $id => $upId) {
			$ret[$id] =  new stdClass();
			
			if (!function_exists('uploadprogress_get_info')) {
				$ret[$id]->message = "In Progress";
				$ret[$id]->percent = "10";
				$ret[$id]->noStatus = true;
				return $ret;
			}

			$tmp = uploadprogress_get_info($upId);
			if (!is_array($tmp)) {
				sleep(1);
				$tmp = uploadprogress_get_info($upId);
				if (!is_array($tmp)) {
					$ret[$id]->message = "Upload Complete";
					$ret[$id]->percent = "100";
					return $ret;
				}
			}

			if ($tmp['bytes_total'] < 1) {
				$percent = 100;
			}
			else {
				$percent = round($tmp['bytes_uploaded'] / $tmp['bytes_total'] * 100, 2);
			}

			if ($percent == 100) {
				$ret[$id]->message = "Complete";
			}

			$eta 		= sprintf("%02d:%02d", $tmp['est_sec'] / 60, $tmp['est_sec'] % 60 );
			$speed 		= $this->_formatBytes($tmp['speed_average']);
			$current 	= $this->_formatBytes($tmp['bytes_uploaded']);
			$total 		= $this->_formatBytes($tmp['bytes_total']);

			$ret[$id]->message = "$eta left (at $speed/sec)	$current/$total($percent%)";
			$ret[$id]->percent = $percent;
		}
		return $ret;
	}

	/**
	 * function to convert bytes to something larger
	 */
	function _formatBytes($x) {
		if ($x < 100)  $x;
		if ($x < 10000)  return sprintf("%.2fKB", $x/1000);
		if ($x < 900000) return sprintf("%dKB", $x/1000);
		return sprintf("%.2fMB", $x/1000/1000);
	}
}
?>
