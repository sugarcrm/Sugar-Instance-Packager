<?php

namespace Sugarcrm\Support\Tests\Helpers {
    // all files in /src directory
    $src = __DIR__ . '/../src/';

    function require_folder($dir)
    {
        $scanned = array_diff(scandir($dir), array('..', '.'));

        foreach ($scanned as $filename) {
            $path = $dir . $filename;

            if (is_file($path)) {
                require_once $path;
            } else {
              $p = "{$dir}{$filename}/";
              if (is_dir($p)) {
                  require_folder($p);
              }
            }
        }
    }

    require_folder($src);
}
