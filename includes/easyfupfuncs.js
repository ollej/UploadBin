// Normalize a UTF-8 string
var utf8n = Object();
utf8n['A%u0308'] = '%C4';
utf8n['a%u0308'] = '%E4';
utf8n['A%u030A'] = '%C5';
utf8n['a%u030A'] = '%E5';
utf8n['O%u0308'] = '%D6';
utf8n['o%u0308'] = '%F6';
// Obsolete, use nfc() instead.
function normalizeUtf8(str) {
    return nfc(str);
    var norm = escape(str);
    for (var i in utf8n) {
        if (utf8n.hasOwnProperty(i)) {
            norm = norm.replace(i, utf8n[i]);
        }
    }
    //norm = norm.replace('A%u0308','%C4');
    //norm = norm.replace('a%u0308','%E4');
    //norm = norm.replace('A%u030A','%C5');
    //norm = norm.replace('a%u030A','%E5');
    //norm = norm.replace('O%u0308','%D6');
    //norm = norm.replace('o%u0308','%F6');
    return unescape(norm);
}

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
    // Make sure string is in normalized UTF8.
    var filename = nfc(f.files.value);
    //console.log('hashing filename:', filename, Basename(filename), SHA256(Basename(filename)));
    //console.log('hashing key:', f.key.value, SHA256(f.key.value));
    f.hashed_name.value = SHA256(Basename(filename));
    f.hashed_key.value = SHA256(f.key.value);
    return true;
}

// Updates the form with a new generated upload key.
function updateKey()
{
    //console.log('updating key');
    new Ajax.Request('?action=getkey', {
    method:'get',
    onSuccess: function(transport) {
        var response = transport.responseText || '';
        $('uploadform').key.value = response;
        //console.log('new key:', response);
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
