<?php

namespace dir2db\deployment;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class Deploy
{
    public static function postPackageInstall($event)
    {   
        if (!is_dir('private')) { 
            mkdir('private');
        }

        if (!file_exists('private/local.ini')) { 
            copy('src/local.ini.example', 'private/local.ini');
        }
    }
}
