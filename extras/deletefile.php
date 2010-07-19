<?php
// Delete files from disk and db.

if (!chdir("/home/uploadbin/uploads")) {
	print "Could not change to uploads directory.\n";
	die;
}

# Connect to MySQL db.
$link = mysql_connect('localhost', 'uploadbin', 'DaheurtIb2');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
mysql_select_db('uploadbin', $link);

$files = array_slice($argv, 1);

# Loop through all files given in arguments and removing them.
foreach ($files as $file) {
	if (file_exists($file)) {
		print "Removing file '$file' from disk...";
		unlink($file);
		print "done\nRemoving file '$file' from db...";
		$query = "DELETE FROM files WHERE hashname = '$file'";
		$result = mysql_query($query, $link);
		print "done\n";
	} else {
		print "File not found: $file\n";
	}
}

