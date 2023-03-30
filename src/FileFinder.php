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
    public ?string $directoryFilter;

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
            $this->directoryFilter = '/'.$directoryFilter.'/i';
        } else {
            $this->directoryFilter = null;
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
        // TODO when using php8 use union return type
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $file = new RegexIterator($iterator, $this->fileFilter);
        $files = array();
        foreach ($file as $info) {
            if (!is_null($this->directoryFilter)) {
                if (!preg_match($this->directoryFilter, $info->getPathname())) {
                    $files[] = $info->getPathname();
                }
            } else {
                $files[] = $info->getPathname();
            }
        }
           
        return $files;
    }
}
