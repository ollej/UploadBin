/**
 * UploadProgressMeter.js - Upload progress Meter javascript code
 *
 * Copyright (C) 2007  Joshua Eichorn
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * I This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 *
 * @author       Joshua Eichorn <josh@bluga.net>
 * @copyright    Joshua Eichorn (c)2005
 * @link         http://bluga.net/projects/upload_progress_meter
 * @version      0.1
 */

/**
 * Global list of ids that were updating progress for
 */
var UploadProgressMeter_list = new Object();

/**
 * List of Currently Active ids
 */
var UploadProgressMeter_active = new Object();

/**
 * Currently Active count
 */
var UploadProgressMeter_count = 0;

/**
 * Update interval for progress bars
 */
var UploadProgressMeter_interval = 2000;

/**
 * ID of the current interval
 */
var UploadProgressMeter_intervalId = false;

/**
 * Remote proxy object
 */
var UploadProgressMeter_remote = false;

/**
 * Does the server return status information
 */
var UploadProgressMeter_status = true;

/**
 * Handling starting up all progress bars when a form submits
 */
function UploadProgressMeter_Start(form) {
	// get an array of all the ids that need to be started, were only looking in the current form
	
	var idsToStart = new Array();

	var divs = form.getElementsByTagName('div');

	for(var i = 0; i < divs.length; i++) {
		var id = divs[i].id;
		if (UploadProgressMeter_list[id]) {
			UploadProgressMeter_count++;
			UploadProgressMeter_active[id] = UploadProgressMeter_list[id];
			UploadProgressMeter_EnableProgress(id);
		}
	}

	if (!UploadProgressMeter_intervalId) {
		if (UploadProgressMeter_status) {
			UploadProgressMeter_intervalId = setInterval(UploadProgressMeter_Update,UploadProgressMeter_interval);
		}
		else {
			UploadProgressMeter_intervalId = setInterval(UploadProgressMeter_Update,200);
		}
	}
	UploadProgressMeter_Update();
}

/**
 * Register a file input by id
 */
function UploadProgressMeter_Register(progressId,identifier) {
	UploadProgressMeter_list[progressId] = identifier;
}

/**
 * Shows a progress bar and sets it to 0
 */
function UploadProgressMeter_EnableProgress(progress_id) {
	var progress = document.getElementById(progress_id);
	progress.style.display = 'block';
	progress.percent = 0;
	progress.start = 0;
	progress.direction = 1;
	progress.message = "Connecting";

	progress.update = function() { 
		this.getFirstDivByClass('bar').style.left = this.start+'%';
		this.getFirstDivByClass('bar').style.width = this.percent+'%'; 
		this.getFirstDivByClass('message').innerHTML = this.message; 
	}

	progress.getFirstDivByClass = function(className) {
		var nodes = this.getElementsByTagName('div');
		for(var i = 0; i < nodes.length; i++) {
			if (nodes[i].className == className) {
				return nodes[i];
			}
		}
	}

	progress.update();
}

/**
 * Update the progress bars of all the current bars
 */
function UploadProgressMeter_Update() {
	if (UploadProgressMeter_count == 0) {
		clearInterval(UploadProgressMeter_intervalId);
		UploadProgressMeter_intervalId = false;
		return;
	}

	if (!UploadProgressMeter_status) {
		for(var i in UploadProgressMeter_active) {
			var el = document.getElementById(i);
			el.percent = 10;
			if (el.start == 90) {
				el.direction = -1;
			}
			if (el.start == 0) {
				el.direction = 1;
			}

			if (el.direction == 1) {
				el.start += 10;
			}
			else {
				el.start -= 10;
			}
			el.update();
		}
		return;
	}

	if (UploadProgressMeter_remote == false) {
		var callback = {
			getStatus: function(result) {
				for(var prop in result) {
					if (prop != "toString") {
						try {
							var el = document.getElementById(prop);
						} catch(e) {
							continue;
						}
						if (!el) {
							continue;
						}
						document.getElementById(prop).percent = result[prop].percent;
						document.getElementById(prop).message = result[prop].message;
						document.getElementById(prop).update();
						if (result[prop].noStatus) {
							UploadProgressMeter_status = false;
							if (UploadProgressMeter_intervalId) {
								clearInterval(UploadProgressMeter_intervalId);
								UploadProgressMeter_intervalId = setInterval(UploadProgressMeter_Update,200);
							}
						}

						if (document.getElementById(prop).percent == 100) {
							UploadProgressMeter_count--;
							delete UploadProgressMeter_active[prop];
						}
					}
				}
			}
		}
		UploadProgressMeter_remote = new UploadProgressMeterStatus(callback);
	}
	UploadProgressMeter_remote.getStatus(UploadProgressMeter_active);
}

window.uploadComplete = function(id,message) {
	UploadProgressMeter_count--;
	delete UploadProgressMeter_active[id];

	document.getElementById(id).start = 0;
	document.getElementById(id).percent = 100;
	document.getElementById(id).message = message;
	document.getElementById(id).update();
}

HTML_AJAX.onError = function(err) {
	document.getElementById('debug').innerHTML += HTML_AJAX_Util.varDump(err);
}
