<?php

use PHPUnit\Framework\TestCase;
use dir2db\FilePathToDatabase;

class Dir2dbTest extends TestCase
{
    private array $argv;

    public function setUp(): void
    {
        $this->argv = explode(',', $_SERVER['argv']);
    }
    
    public function test_options_captured_correctly()
    {
        $testPath = "/testdir";
        $testRegex = "/\.(?:php)$/";
        $testExclusions = "vendor|node_modules|private";

        $this->assertStringContainsString($testPath, $this->argv[0]);
        $this->assertStringContainsString($testRegex, $this->argv[1]);
        $this->assertStringContainsString($testExclusions, $this->argv[2]);
    }
}
