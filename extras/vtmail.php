<?php
// Send file to VirusTotal.com for virus scanning.

require_once 'Zend/Mail.php';

if (!chdir("/home/uploadbin/uploads")) {
	print "Could not change to uploads directory.\n";
	die;
}

$files = array_slice($argv, 1);

# Loop through all files given in arguments and removing them.
foreach ($files as $file) {
	if (!file_exists($file)) {
		print "File not found: $file\n";
		continue;
	}
	if (filesize($file) > 20971520) {
		print "File '$file' too big, skipping...\n";
		continue;
	}
	print "Sending file '$file' to VirusTotal.com for virus scan...";
	$data = file_get_contents($file);
	$mail = new Zend_Mail();
	$mail->setFrom('Olle@Johansson.com');
	$mail->setSubject('SCAN');
	$mail->setBodyText($file);
	$mail->addTo('scan@virustotal.com');
	$mail->createAttachment($data);
	$mail->send();
	print "done\n";
}

