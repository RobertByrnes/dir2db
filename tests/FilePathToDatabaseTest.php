<?php

declare(strict_types=1);

use dir2db\FileFinder;
use dir2db\FilePathToDatabase;
use PHPUnit\Framework\TestCase;

final class FilePathToDatabaseTest extends TestCase
{
    use FileFinder;

    private array $argv;
    private string $dirSeparator = '/';

    public function setUp(): void
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->dirSeparator = '\\';
        }

        $this->argv = explode(',', $_SERVER['argv']);
    }

    public function test_file_finder_returns_expected_array_of_php_files(): void
    {
        $testPath = __DIR__.$this->argv[0];
        $testRegex = $this->argv[1];
        $testExclusions = $this->argv[2];

        $expected = [
            $testPath.$this->dirSeparator.'dir2db.php',
            $testPath.$this->dirSeparator.'Environment.php',
            $testPath.$this->dirSeparator.'FileFinder.php',
            $testPath.$this->dirSeparator.'FilePathToDatabase.php'
        ];

        $expected = $this->lowerCaseFilePaths($expected);
        $files = $this->lowerCaseFilePaths($this->fileFinder($testPath, $testRegex, $testExclusions));

        foreach ($expected as $file) {
            $this->assertContains($file, $files);
        }
    }

    public function test_file_finder_returns_expected_array_of_sql_files(): void
    {
        $testPath = __DIR__.$this->argv[0];
        $testRegex = '/\.(?:sql)$/';
        $testExclusions = $this->argv[2];

        $expected = [
            $testPath.$this->dirSeparator.'dir2db.sql'
        ];

        $files = $this->fileFinder($testPath, $testRegex, $testExclusions);
        
        foreach ($expected as $file) {
            $this->assertContains($file, $files);
        }
    }

    public function test_file_finder_returns_expected_array_of_different_file_types(): void
    {
        $testPath = __DIR__.$this->argv[0];
        $testRegex = '/\.(?:sql|php|example)$/';
        $testExclusions = $this->argv[2];

        $expected = [
            $testPath.$this->dirSeparator.'dir2db.php',
            $testPath.$this->dirSeparator.'dir2db.sql',
            $testPath.$this->dirSeparator.'Environment.php',
            $testPath.$this->dirSeparator.'FileFinder.php',
            $testPath.$this->dirSeparator.'FilePathToDatabase.php',
            $testPath.$this->dirSeparator.'local.ini.example'
        ];

        $expected = $this->lowerCaseFilePaths($expected);
        $files = $this->lowerCaseFilePaths($this->fileFinder($testPath, $testRegex, $testExclusions));

        foreach ($expected as $file) {
            $this->assertContains($file, $files);
        }
    }

    public function test_no_directory_exclusion_if_null(): void
    {
        $testPath = __DIR__.$this->argv[0];
        $testRegex = $this->argv[1];    
        
        $expected = [
            $testPath.$this->dirSeparator.'dir2db.php',
            $testPath.$this->dirSeparator.'Environment.php',
            $testPath.$this->dirSeparator.'FileFinder.php',
            $testPath.$this->dirSeparator.'FilePathToDatabase.php',
            $testPath.$this->dirSeparator.'vendor/dir2db.php'
        ];     
        
        $expected = $this->lowerCaseFilePaths($expected);
        $files = $this->lowerCaseFilePaths($this->fileFinder($testPath, $testRegex));

        foreach ($expected as $file) {
            $this->assertContains($file, $files);
        }
    }
    
    private function lowerCaseFilePaths(array $files): array
    {
        foreach ($files as &$file) {
            $file = strtolower($file);
        }
        return $files;
    }
}
