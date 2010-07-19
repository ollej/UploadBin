<?php
/**
 * VirusChecker scanner plugin to check for viruses using clamscan.
 * @author Olle@Johansson.com
 */

class VCScanner_ClamScan implements VCScanner
{
    /**
     * Runs clamscan on the given file.
     */
    public function scan($filename) {
        exec('clamscan --no-summary --infected --scan-archive=yes ' . escapeshellarg($filename), $output = array(), $retval);
        if ($retval === 0) {
            return 0;
        } else if ($retval === 1) {
            return 1;
        } else {
            throw new Exception("VirusChecker ClamScan encountered an error ($retval):\n" . implode('', $output));
        }
    }
}
