<?php

namespace dir2db;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class Deploy
{
    public static function postPackageInstall($event)
    {   
        if (!is_dir(__DIR__.'/../private') && !mkdir(__DIR__.'/../private') && !is_dir(__DIR__.'/../private')) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', '../private'));
        }

        if (!file_exists('../private/local.ini')) {
            copy(__DIR__.'/local.ini.example', __DIR__.'/../private/local.ini');
        }
    }
}
