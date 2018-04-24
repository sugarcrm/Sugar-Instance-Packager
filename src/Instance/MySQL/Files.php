<?php

namespace Sugarcrm\Support\Helpers\Packager\Instance\MySQL;

class Files extends \Sugarcrm\Support\Helpers\Packager\Instance\Abstracted\Files
{
    public function __construct($sugarPath,  $destinationFolder, $archiveName)
    {
        return parent::__construct($sugarPath, $destinationFolder, $archiveName);
    }
}
