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
 * A class to check a file for viruses.
 */
class VirusChecker
{
	public $filename;
    private $config;
    private $scanners;

    const VCDIR = 'classes/VCScanner';

	/**
	 * Create a new VirusChecker instance based on a filename (including full path).
	 * 
	 * @param string $filename Full path and name of file to check.
	 */
	function __construct($filename = NULL, $config = NULL) {
	    $this->filename = $filename;
        $this->config = $config;
        $this->readScanners();
        return $this;
	}

    function setFilename($filename) {
        $this->filename = $filename;
        return $this;
    }

    function isReadable() {
        if (!$this->filename) return -1;
        if (!file_exists($this->filename)) return -2;
        if (!is_readable($this->filename)) return -3;
        return 1;
    }

    function readScanners() {
        $this->scanners = array();
        $scannerlist = $this->getScannerList();
        foreach ($scannerlist as $s) {
            $vc = $this->loadScanner($s);
            array_push($this->scanners, $vc);
        }
        return $this;
    }

    function getScannerList() {
        if (is_string($this->config)) {
            $scannerlist = explode(',', $this->config);
        } else {
            $scannerlist = explode(',', $this->config->viruscheckers);
        }
        if (empty($scannerlist)) {
            throw new Exception("VirusChecker needs a list of one or more scanner plugins to work.");
        }
        return $scannerlist;
    }

    function loadScanner($name) {
        $classname = 'VCScanner_' . trim($name);
        $path = VirusChecker::VCDIR . '/' . $name . '.php';
        if (!interface_exists('VCSanner')) {
            include_once('classes/VCScanner.php');
        }
        try {
            include_once($path);
            $vc = new $classname();
            $rc = new ReflectionClass($classname);
            if (!$rc->implementsInterface('VCScanner')) {
                throw new Exception("VirusChecker scanner plugin doesn't implement VCScanner interface: " . $name);
            }
        } catch (Exception $e) {
            throw new Exception("VirusChecker couldn't load scanner: " . $path . "\nMessage: " . $e->getMessage());
        }
        return $vc;
    }

    function scan() {
        $score = 0;
        if (!$ret = $this->isReadable()) {
            throw new Exception("VirusChecker couldn't read file ($ret): " . $this->filename);
        }
        if (count($this->scanners) <= 0) {
            throw new Exception("VirusChecker hasn't got any loaded scanners.");
        }
        foreach ($this->scanners as $s) {
            $score += $s->scan($this->filename);
        }
        return $score;
    }
}

