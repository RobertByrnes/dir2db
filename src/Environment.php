<?php

namespace dir2db;

Abstract Class Environment
{
    public string $path = '/../private/local.ini';

    protected function parseIni() : array
    {
        if (!file_exists(__DIR__.$this->path)) {
            die("local.ini file is missing");
        }

        $env = parse_ini_file(__DIR__.$this->path);
        $GLOBALS['environment'] = 'TRUE';
        $GLOBALS['dsn'] = 'mysql:dbname='.$env['dbname'].';host='.$env['host'];
        $GLOBALS['username'] = $env['username'];
        $GLOBALS['password'] = $env['password'];
        return $GLOBALS;
    }
}
