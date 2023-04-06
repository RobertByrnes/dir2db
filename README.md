<h1 align="center">dir2db</h1>

<p align="center">

<img src="https://img.shields.io/badge/made%20by-RobertByrnes-blue.svg" >

<img src="https://img.shields.io/badge/stability-wip-red.svg" >

<!-- <img src="https://img.shields.io/npm/v/vue2-baremetrics-calendar">

<img src="https://img.shields.io/badge/vue-2.6.10-green.svg"> -->

<!-- <img src="https://badges.frapsoft.com/os/v1/open-source.svg?v=103" >

<img src="https://img.shields.io/github/stars/silent-lad/Vue2BaremetricsCalendar.svg?style=flat">

<img src="https://img.shields.io/github/languages/top/silent-lad/Vue2BaremetricsCalendar.svg">

<img src="https://img.shields.io/github/issues/silent-lad/Vue2BaremetricsCalendar.svg"> -->

<img src="https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat">
</p>

![dir2db!](dir2db.jpg?raw=true "dir2db!")


# dir2db
- Uses PHP recursive iterator classes and regex iterator class to recursively search directories for .php files.

- Any file extension may be searched for, or a group of them by passing in regex. See help description (run dir2db.php in cmd)

- Certain named directories may be excluded from the search, again by passing in regex through cmd (see help in dir2db.php)

## Setup
Install via ``` git clone https://github.com/RobertByrnes/dir2db.git ``` then cd into package root directory and run ``` composer deploy-ini ```


Use dir2db.sql to install ```file_contents``` table to a mySQL database.

For example run
```mysql -u {$databaseUser} -p {$databaseName} < dir2db.sql```

Edit ./private/local.ini to include correct database credentials.

## Useage
In CMD/terminal type 'php dir2db.php', this will show the help menu:
```
    /*** ARGUMENTS ***/
    Required arguments:
        -p path, e.g. c:/wamp/www/
    Optional arguments: 
        -r regex, to filter file search to only include certain file extension. Default: /\.(?:php)$/
        -e exclusions, directories ommited during search e.g. vendor|node_modules|private *Must include pipe*
        -h help, prints this help message.";
```
## Examples
1. `php dir2db.php -p c:/wamp/www/repositories`
2. `php dir2db.php -p c:/wamp/www/repositories -r "/\.(?:txt)$/"`
3. `php dir2db.php -p c:/wamp/www/repositories -r "/\.(?:txt)$/" -e vendor|node_modules`
4. `php dir2db.php -p c:/wamp/www/repositories -r "/\.(?:txt|ini|sql)$/" -e vendor|node_modules`

- Example 1 will search all directories within repositories for php files
- Example 2 will search for .txt file within repositories
- Example as example 2, excluding anything within directories named 'vendor' or 'node_modules'
- Example four show an example of using the regex to include multiple file extensions

## Memory Limit!
If you see a fatal exception - Allowed Memory Limit rerun your command explicity as below:
- ```php .\dir2db.php -p c:/dir2db -r "/\.(?:jpg|png|pdf|php)$/" -e "vendor|node_modules"```
- ``` php -d memory_limit=-1 .\dir2db.php -p c:/dir2db -r "/\.(?:jpg|png|pdf|php)$/" -e "vendor|node_modules"```
  
The ```-d memory_limit=-1``` will tell php to ignore the memory limit whilst executing this process.

## FileFinder Trait

The FileFinder trait uses PHP's recursive iterator classes to search for files in a directory structure returning an array of results.

## Usage

To use the FileFinder trait, call the `fileFinder` method with the following parameters:

- `$path` - The path to the directory you want to search
- `$fileFilter` (optional) - A regex to find PHP file extensions
- `$directoryFilter` (optional) - Directory names to exclude from the search

The `fileFinder` method will then return an array of results.
## Wish List
- Refactor .sql file to not be file type specific, e.g. rename column `content` rather than `php_files_complete`
- Extend tests to cover FilePathToDatabase.php


![Files!](files.png?raw=true "Files!")