<?php

namespace Sugarcrm\Support\Helpers\Packager\Instance\MySQL;

class Files extends \Sugarcrm\Support\Helpers\Packager\Instance\Abstracted\Files
{
    public function __construct($sourceFolder, $destinationFolder, $archiveName)
    {
        return parent::__construct($sourceFolder, $destinationFolder, $archiveName);
    }
}