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
 * VirusChecker scanner plugin to check MD5/SHA1 hash against VirusTotal.com
 * @author Olle@Johansson.com
 */
class VCScanner_VirusTotalHash implements VCScanner
{
    const URL = 'http://www.virustotal.com/vt/en/consultamd5';
    const GET = 1;
    const POST = 2;
    const MATCH_THRESHOLD = 2;

    /**
     * Posts the MD5 hash of given file to VirusTotal and parses the resulting page to see if it is a known virus file.
     */
    public function scan($filename) {
        $md5 = md5_file($filename);
        $data = $this->getUrl(
            VCScanner_VirusTotalHash::URL, 
            VCScanner_VirusTotalHash::POST, 
            array(
                'hash' => $md5,
            )
        );
        if (!$data) {
            #throw new Exception("VirusTotal.com request didn't return any data!");
			return 0;
        }
        if ($this->parsePage($data)) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Parses the given string to see if a virus has been found.
     * @param string $data String to check for virus information.
     */
    private function parsePage($data) {
        $re = '<div id="status_porcentaje">Result: <span id="porcentaje"><span style="color:(\w+);">(\d+)</span>/(\d+) \([\d\.]+%\)</span></div>';
        $matches = array();
        #print "data:\n$data\n";
        if (preg_match("#$re#", $data, $matches)) {
            #print "Matches: " . print_r($matches, true);
            if (($matches[2] / $matches[3] * 100) > VCScanner_VirusTotalHash::MATCH_THRESHOLD) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gets the content of the $url using CURL.
     * @param string $url URL to download.
     * @param int HTTP method to use.
     * @param array $params List of POST parameters to send.
     */
    private function getUrl($url, $method=VCScanner_VirusTotalHash::GET, $params=array()) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLINFO_HEADER_OUT, true); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if ($method === VCScanner_VirusTotalHash::POST) {
            curl_setopt($ch, CURLOPT_POST, true);
            $encoded_params = $this->encodeUrlParams($params);
            #print "params: $encoded_params\n";
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded_params);
        }
        $output = curl_exec($ch);
        #print "HTTP Status: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
        #print "HEADERS:\n" . curl_getinfo($ch, CURLINFO_HEADER_OUT) . "\n";
        curl_close($ch);
        return $output;
    }

    /**
     * Encodes $params array into URL paramaters.
     * @param array $params Associative array with key/value pairs to encode into URL paramaters.
     */
    private function encodeUrlParams($params) {
        $p = "";
        foreach ($params as $k => $v) {
            $p .= $p ? '&' : '';
            $p .= "$k=" . urlencode($v);
        }
        return $p;
    }
}
