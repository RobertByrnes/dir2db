# Directory_to_Database
- Uses PHP recursive iterator classes and regex iterator class to recursively search directories for .php files.

- Any file extension may be searched for, or a group of them by passing in regex. See help description (run dir2db.php in cmd)

- Certain named directories may be excluded from the search, again by passing in regex through cmd (see help in dir2db.php)

# Setup
- Use /dir2db.sql to install table to mySQL database.  This is a requirement.

- Edit local.ini to include correct database credentials and move this file to a directory named private  one level up from the document root.

# Use
- In CMD/terminal type 'php dir2db.php', this will show the help menu:

    /*** ARGUMENTS ***/
        Required arguments:
            -p path, e.g. c:/wamp/www/
        Optional arguments: 
            -r regex, to filter file search to only include certain file extension. Default: /\.(?:php)$/ *Do not wrap in quotes* 
            -e exclusions, directories ommited during search e.g. vendor|node_modules|private *Must include pipe*
            -h help, prints this help message.";

# Example
1. php dir2db.php -p c:/wamp/www/repositories
2. php dir2db.php -p c:/wamp/www/repositories -r /\.(?:txt)$/
3. php dir2db.php -p c:/wamp/www/repositories -r /\.(?:txt)$/ -e vendor|node_modules

- Example 1 will search all directories within repositories for php files
- Example 2 will search for .txt file within repositories
- Example as example 2, excluding anything within directories named 'vendor' or 'node_modules'


