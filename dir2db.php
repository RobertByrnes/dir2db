<?php

require_once('vendor/autoload.php');

$shortopts = "p:r::e::vh";

$longopts  = array(
    "path:",
    "regex:",
    "exclusions::",
    "version",
    "help"
);

$options = getopt($shortopts, $longopts);

switch (TRUE)
{
    case isset($options['p']):     $path = $options['p']; break;
    case isset($options['v']):     printVersion(); break;
    case isset($options['h']):     help(); break;
}

(empty($options['e'])) ? $exludeDirs = NULL : $exludeDirs = $options['e'];
(empty($options['r'])) ? $regex = NULL : $regex = $options['r'];

(!empty($path)) ? run($path, $exludeDirs, $regex) : help();

function run($path, $exludeDirs=NULL, $regex=NULL) : void
{
    $program = new FilePathToDatabase($path, $exludeDirs, $regex);
}

function help()
{
    $helpMessage = "\n
    /*** ARGUMENTS ***/\n
    Required arguments:
        -p path (string) e.g. c:/wamp/www/
    Optional arguments: 
        -r regex (string) to filter file search to only include certain file extension. Default is '/\.(?:php)$/'
        -e excluded directories (string) directories ommited during search e.g. vendor|node_modules|private **must include pipe**
        -v returns the version.
        -h prints this help message.";
    print("\n".$helpMessage."\n");
    die();
}

function printVersion()
{
    print("\n[+] Directory to Database, written by Robert Byrnes,
    under GPLv3 licence. https://github/RobertByrnes/Directory_to_Database\n");
    die();
}