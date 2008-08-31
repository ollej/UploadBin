function Basename(path)
{
    return path.replace( /.*(\/|\\)/, "" );
}

function Payload()
{
	//$('loadingimage').toggle();
	var f = document.uploadform;
	f.hashed_name.value = hex_sha256(Basename(f.files.value));
	f.hashed_key.value = hex_sha256(f.key.value);
	return true;
}

// Loads content into the content div
function loadContent( page ) {
	self.document.location.hash = page;
	new Ajax.Updater('content', page);
}

// Initialize some things when the page loads.
document.observe("dom:loaded", function() {
	$('advancedDiv').toggle();
	$('loadingimage').toggle();
	document.uploadform.files.focus();
	var hashvalue = self.document.location.hash.substring(1);
	if (hashvalue != "")
	{
		new Ajax.Updater('content', hashvalue);
	}
});
