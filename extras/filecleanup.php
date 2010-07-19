<?php

// Cleanup files for easyfup

if (!chdir("/home/uploadbin/uploads")) {
	print "Could not change to uploads directory.\n";
	die;
}

print "Dummy run, remove comments in code to do actual cleanup.\n";

# Connect to MySQL db.
$link = mysql_connect('localhost', 'uploadbin', 'DaheurtIb2');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
mysql_select_db('uploadbin', $link);


# Read file list and check if files are found on disk.
$deletefiles = array();
$query = "SELECT id, filename, hashname FROM files ORDER BY id ASC";
$result = mysql_query($query, $link);
while ($row = mysql_fetch_assoc($result)) {
	if (!file_exists($row['hashname'])) {
		print "File not found: " . $row['filename'] . " (" . $row['hashname'] . ")\n";
		$deletefiles[] = $row['id'];
	}
}

# Remove all missing files from db.
$query = "DELETE FROM files WHERE id IN (" . join(',', $deletefiles) . ")";
print $query . "\n\n";
#$result = mysql_query($query, $link);

# Read all files and make sure they are in the db.
foreach (glob("*") as $filename) {
	$query = "SELECT COUNT(id) FROM files WHERE hashname = '$filename'";
	$result = mysql_query($query, $link);
	$found = mysql_result($result, 0);
	if ($found == 0) {
		print "Removing file not in db: " . $filename . "\n";
		#unlink($filename);
	}
}
