<?php

require_once('vendor/autoload.php');

print("
                        ______     _             _____    ______   ______     
                       |_   _ `.  (_)           / ___ `. |_   _ `.|_   _ \    
                         | | `. \ __   _ .--.  |_/___) |   | | `. \ | |_) |   
                         | |  | |[  | [ `/''\]  .'.....'   | |  | | |  __'.   
                        _| |_.' / | |  | |     / /_____   _| |_.' /_| |__) |  
                       |______.' [___][___]    |_______| |______.'|_______/   
                                                                            
");
print("
                                            .----.______
                                            |rbdev      |
                                            |    ___________
                                            |   /          /
                                            |  /          /
                                            | /          /
                                            |/__________/

");

$shortopts = "p:r::e::h";

$longopts  = array(
    "path:",
    "regex:",
    "exclusions::",
    "help"
);

$options = getopt($shortopts, $longopts);

switch (TRUE)
{
    case isset($options['p']):     $path = $options['p']; break;
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
        -p path, e.g. c:/wamp/www/
    Optional arguments: 
        -r regex, to filter file search to only include certain file extension. Default: /\.(?:php)$/ *Do not wrap in quotes* 
        -e exclusions, directories ommited during search e.g. vendor|node_modules|private *Must include pipe*
        -h help, prints this help message.";
    print("\n".$helpMessage."\n");
    print("\n    Dir2DB, written by Robert Byrnes,
    under GPLv3 licence. https://github/RobertByrnes/Directory_to_Database\n");
    die();
}