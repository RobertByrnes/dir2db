<?php

namespace dir2db;

class Deploy
{
    public static function postPackageInstall(): void
    {   
        if (!is_dir(__DIR__.'/../private') && !mkdir(__DIR__.'/../private') && !is_dir(__DIR__.'/../private')) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', '../private'));
        }

        if (!file_exists('../private/local.ini')) {
            copy(__DIR__.'/local.ini.example', __DIR__.'/../private/local.ini');
        }
    }
}

Deploy::postPackageInstall();
