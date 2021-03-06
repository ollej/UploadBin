<?php
/**
 * UploadBin - Site for sending files.
 *
 * @package uploadbin
 * @author Olle@Johansson.com and Mattias Johansson
 */

/*
The MIT License

Copyright (c) 2010 Olle Johansson

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

/**
 * VirusChecker scanner plugin to check for viruses using F-Prot scanner..
 * @author Olle@Johansson.com
 */
class VCScanner_FProtScan implements VCScanner
{
    /**
     * Runs clamscan on the given file.
     */
    public function scan($filename) {
        $output = array();
        $lastline = exec('fpscan -v 0 ' . escapeshellarg($filename), $output, $retval);
        $output = implode('', $output);
	#print "retval: $retval\nlastline: $lastline\noutput: $output\n";
        if (($retval !== 1 && $retval !== 2 && $retval !== 3) || empty($lastline) || strpos($lastline, '[Found') === false) {
            return 0;
        } else {
            return 1;
        }
    }
}
