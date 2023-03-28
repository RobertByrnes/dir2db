<?php

namespace dir2db;

/**
 * Class FilePathToDatabase uses Triat FileFinder to recursively search a directory structure
 * for PHP files, inserting the contents into a database, reading any class names found.
 * FilePathToDatabase is written to be executed on cli.
 */
class FilePathToDatabase extends DataConnection
{
    use FileFinder;

    /**
     * Array containing filepaths returned from FileFinder.
     * 
     * @var array
     */
    public array $files;

    /**
     * A count of any database insert errors.
     * 
     * @var int
     */
    public int $errorCount;

    private FileFinder $fileFinder;

    /**
     * Class Constructor.
     */
    public function __construct($path, $exludeDirs=NULL, $regex=NULL)
    {
        parent::__construct();
        echo "\n[+] Searching directories for .php files\n\n";
        $this->files = $this->fileFinder($path, $exludeDirs, $regex);
        if (count($this->files) > 0)
        {
            $count = count($this->files);
            print("[+] ".$count." Files found.\n\n");
            sleep(2);
            $this->insertFilesToDatabase();
        }
        else
        {
            print("[-] No files found, exiting.");
            exit;
        }
    }

    /**
     * Gets file contents from each file and inserts the contents a the database as a string
     * along with the full filepath.
     *
     * @return void
     */
    private function insertFilesToDatabase() : void
    {
        print("[+] Inserting files into database.\n\n");
        sleep(2);
        $this->errorCount = 0;
        $rowCount = 0;
        foreach ($this->files as $file)
        {
            $fileContents = file_get_contents($file);
            $tempRowCount = $this->preparedInsertGetCount("INSERT INTO `php_files_complete` (`file_path`, `complete_file`) VALUES (?, ?)", array($file, $fileContents));  
            if ($tempRowCount === 0)
            {
                ++$this->errorCount;
                echo "[-] File inserted: ".$file."\n";
                echo "[-] Error count: ".$this->errorCount."\n";
            }
            else
            {
                $rowCount += $tempRowCount;
                echo "[+] File inserted: ".$file."\n";
                echo "[+] Row count: ".$rowCount."\n";
            }
            $this->getClassNameFromFile($file);  
        }
        print("[+] Script execution complete, exiting.");
    }

    /**
     * Reads through each file using PHP tokens to discover class names, inserting any class
     * name's discovered into the row for this class.
     *
     * @param string $file
     * @return void
     */
    public function getClassNameFromFile($file) : void
    {
        error_reporting(~E_NOTICE & ~E_WARNING); 
        $pointer = fopen($file, 'r');
        $class = $namespace = $buffer = '';
        $i = 0;
        while (!$class)
        {
            if (feof($pointer)) break;

            $buffer .= fread($pointer, 512);
            $tokens = @token_get_all($buffer);

            if (strpos($buffer, '{') === FALSE) continue;

            for (;$i<count($tokens);$i++)
            {
                if ($tokens[$i][0] === T_NAMESPACE)
                {
                    for ($j=$i+1;$j<count($tokens); $j++)
                    {
                        if ($tokens[$j][0] === T_STRING)
                        {
                            $namespace .= '\\'.$tokens[$j][1];
                        }
                        else if ($tokens[$j] === '{' || $tokens[$j] === ';')
                        {
                            break;
                        }
                    }
                }

                if ($tokens[$i][0] === T_CLASS)
                {
                    for ($j=$i+1;$j<count($tokens);$j++)
                    {
                        if ($tokens[$j] === '{')
                        {
                            $class = $tokens[$i+2][1];
                        }
                    }
                }
            }
        }
        if (is_string($class))
        {
            $this->preparedInsertGetCount("UPDATE `php_files_complete` SET class_name = ? WHERE `file_path` = ?", array($class, $file));
            return;
        }
        error_reporting(-1);
        return;
    }
}