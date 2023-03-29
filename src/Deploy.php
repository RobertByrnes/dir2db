<?php

namespace dir2db;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class Deploy
{
    public static function postPackageInstall($event)
    {   
        if (!is_dir('../private') && !mkdir('../private') && !is_dir('../private')) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', '../private'));
        }

        if (!file_exists('../private/local.ini')) {
            copy('src/local.ini.example', 'private/local.ini');
        }
    }
}
