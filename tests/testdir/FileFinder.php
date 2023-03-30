<?php

namespace dir2db;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use RegexIterator;

/**
 * Trait FileFinder uses PHP's recursive iterator classes to search for PHP files in a directory
 * structure returning an array of results.
 */
trait FileFinder
{
    /**
     * Directory names to exclude from the search.
     * 
     * @var string
     */
    public string $directoryFilter = '/vendor|node_modules/i';

    /**
     * Regex to find PHP file extension.
     * 
     * @var string
     */
    public string $fileFilter = '/\.(?:php)$/';

    /**
     * The callable function of Trait FileFinder, calls $this recursiveRegexIterator.
     *
     * @param string $path
     * @param string $fileFilter
     * @param string $directoryFilter
     * @return array
     */
    public function fileFinder(string $path, string $fileFilter=null, string $directoryFilter=null) : array
    {
        if (!is_null($fileFilter)) {
            $this->fileFilter = $fileFilter;
        }

        if (!is_null($directoryFilter)) {
            $this->directoryFilter = $directoryFilter;
        }

        return $this->recursiveRegexIterator($path);
    }

    /**
     * Iteratives recursively through directories using regex to filter file results.
     *
     * @param string $path
     * @return array
     */
    public function recursiveRegexIterator(string $path) : array
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $phpFile = new RegexIterator($iterator, $this->fileFilter);
        
        $files = array();
        
        foreach ($phpFile as $info) {
            if (!preg_match($this->directoryFilter, $info)) {
                $files[] = $info->getPathname();
            }
        }

        if (!empty($files)) {
            return $files;
        }

        $files[] = 'No files discovered within search parameters.';
        return $files;
    }
}
