<?php

namespace dir2db;

use Dir2dbException;
/**
 * Class FilePathToDatabase uses Triat FileFinder to recursively search a directory structure
 * for PHP files, inserting the contents into a database, reading any class names found.
 * FilePathToDatabase is written to be executed on cli.
 */
class FilePathToDatabase extends DataConnection
{
    use FileFinder;

    /** Array containing filepaths returned from FileFinder @var array */
    public array $files;

    /** A count of any database insert errors @var int */
    public int $errorCount;

    public function __construct(string $path, string $exludeDirs=null, string $regex=null)
    {
        parent::__construct();
        
        echo "[+] Searching directories for files" . PHP_EOL;

        $this->files = $this->fileFinder($path, $regex, $exludeDirs);

        if (count($this->files) > 0) {
            $count = count($this->files);

            echo "[+] " . $count . " Files found." . PHP_EOL;
            
            sleep(2);
            $this->insertFilesToDatabase();
        } else {
            echo "[-] No files found, exiting.";
            exit;
        }
    }

    /**
     * Gets file contents from each file and inserts the contents a
     * the database as a string along with the full filepath.
     *
     * @return void
     */
    private function insertFilesToDatabase(): void
    {
        echo "[+] Attempting to insert files into database." . PHP_EOL;

        sleep(2);

        $this->errorCount = 0;
        $rowCount = 0;

        foreach ($this->files as $file) {
            if (!file_exists($file)) {
                echo "[-] File @: " . $file . " does not exist!" . PHP_EOL;
                continue;
            }

            $fileContents = file_get_contents($file);

            if ($fileContents === false) {
                echo "[-] Couldn't get file contents for file @: " . $file . PHP_EOL;
                continue;
            }

            $sql = "INSERT INTO `php_files_complete` (`file_path`, `complete_file`) VALUES (?, ?)";

            try {
                $tempRowCount = $this->preparedInsertGetCount($sql, array($file, $fileContents));  
            } catch (Dir2dbException $e) {
                echo "[-] File insert failed: " . $file . ', ' . $e->getMessage() . PHP_EOL;
                continue;
            }
            
            if ($tempRowCount === 0) {
                $this->errorCount++;
                echo "[-] File insert failed: " . $file . PHP_EOL . "[-] Error count: " . $this->errorCount . PHP_EOL;
            } else {
                $rowCount += $tempRowCount;
                echo "[+] File inserted: " . $file . PHP_EOL . "[+] Row count: " . $rowCount . PHP_EOL;
            }

            if ($rowCount > 0) {
                if (array_key_last(explode('.', $file)) === 'php') {
                    $this->getClassNameFromFile($file);
                }
            }
        }
        echo "[+] Script execution complete, exiting." . PHP_EOL;
    }

    /**
     * Reads through each file using PHP tokens to discover class names, inserting any class
     * name's discovered into the row for this class.
     *
     * @param string $file
     * @return void
     */
    public function getClassNameFromFile(string $file): void
    {
        error_reporting(~E_NOTICE & ~E_WARNING); 

        $pointer = fopen($file, 'r');
        $class = $namespace = $buffer = '';
        $i = 0;

        while (!$class) {
            if (feof($pointer)) {
                break;
            };

            $buffer .= fread($pointer, 512);
            $tokens = @token_get_all($buffer);

            if (strpos($buffer, '{') === false) {
                continue;
            }

            // for (;$i<count($tokens);$i++) {
            //     if ($tokens[$i][0] === T_NAMESPACE) {
            //         for ($j=$i+1;$j<count($tokens); $j++) {
            //             if ($tokens[$j][0] === T_STRING) {
            //                 $namespace .= '\\'.$tokens[$j][1];
            //             } else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
            //                 break;
            //             }
            //         }
            //     }

            //     if ($tokens[$i][0] === T_CLASS) {
            //         for ($j=$i+1;$j<count($tokens);$j++) {
            //             if ($tokens[$j] === '{') {
            //                 $class = $tokens[$i+2][1];
            //             }
            //         }
            //     }
            // }

            for ($i=0, $j=0;$i<count($tokens);$i++) {
                if ($tokens[$i][0] === T_NAMESPACE) {
                    for ($j=$i+1;$j<count($tokens); $j++) {
                        if ($tokens[$j][0] === T_STRING) {
                            $namespace .= '\\'.$tokens[$j][1];
                        } else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                            break;
                        }
                    }
                }
            
                if ($tokens[$i][0] === T_CLASS && $tokens[$i+2][1]) {
                    $class = $tokens[$i+2][1];
                }
            }
        }
        error_reporting(-1);

        if (!empty($class)) {
            $sql = "UPDATE `php_files_complete` SET class_name = ? WHERE `file_path` = ?";
            try {
                $this->preparedInsertGetCount($sql, array($class, $file));
            } catch (Dir2dbException $e) {
                echo "[-] File insert failed: " . $file . ', ' . $e->getMessage() . PHP_EOL;
            }
        }
    }
}
