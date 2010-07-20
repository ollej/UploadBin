#!/usr/bin/php
<?php
require 'classes/VirusChecker.php';

if (!chdir("/home/uploadbin/uploads")) {
	print "Could not change to uploads directory.\n";
	die;
}

if (count($argv) > 1) {
	$files = array_slice($argv, 1);
	foreach ($files as $filename) {
		checkFile($filename);
	}
} else {
	foreach (glob("*") as $filename) {
		checkFile($filename);
	}
}

function checkFile($filename) {
	print "$filename...";
	$vc = new VirusChecker($filename, 'VirusTotalHash');
	if ($vc->scan()) {
		print "INFECTED\n";
	} else {
		print "OK\n";
	}
}

