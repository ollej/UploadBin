// Returns the basename part of path
function Basename(path)
{
    return path.replace( /.*(\/|\\)/, "" );
}

// Calculates Payload hashes using sha256
function Payload()
{
	//$('loadingimage').toggle();
    var f = $('uploadform');
    f.hashed_name.value = hex_sha256(Basename(f.files.value));
    f.hashed_key.value = hex_sha256(f.key.value);
	return true;
}

// Updates the form with a new generated upload key.
function updateKey()
{
    new Ajax.Request('?action=getkey', {
	method:'get',
	onSuccess: function(transport) {
	    var response = transport.responseText || '';
	    $('uploadform').key.value = response;
	}
    });
}

// Loads content into the content div
function loadContent( page ) {
	self.document.location.hash = page;
	new Ajax.Updater('content', page, {
		onComplete: function () {
			Effect.ScrollTo('content', { duration: 0.2 });	
		}
	});
	
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
