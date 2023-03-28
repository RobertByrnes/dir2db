<?php

abstract class FilesystemRegexFilter extends RecursiveRegexIterator {
    protected $regex;
    public function __construct(RecursiveIterator $it, $regex) {
        $this->regex = $regex;
        parent::__construct($it, $regex);
    }
}

class FilenameFilter extends FilesystemRegexFilter {
    // Filter files against the regex
    public function accept() {
        return ( ! $this->isFile() || preg_match($this->regex, $this->getFilename()));
    }
}

class DirnameFilter extends FilesystemRegexFilter {
    // Filter directories against the regex
    public function accept() {
        return ( ! $this->isDir() || preg_match($this->regex, $this->getFilename()));
    }
}

$directory = new RecursiveDirectoryIterator('C:/wamp64/www/repositories');
// Filter out ".Trash*" folders
$filter = new DirnameFilter($directory, '/^(?!\.Trash)/');
// Filter PHP/HTML files 
$filter = new FilenameFilter($filter, '/^.+\.php$/i');

foreach(new RecursiveIteratorIterator($filter) as $file) {
    if(preg_match('/.php/', $file) && !preg_match('/smarty|vendor|deepscripts|blackeye|phpBrute|phpChart_light|parallel/i', $file))
    {
        echo $file . PHP_EOL;

    }
}
// /^.+\.php$/i
// /\.(?:php)$/