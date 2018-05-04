<?php

namespace Sugarcrm\Support\Helpers\Packager\Instance\Cloud;

class Files extends \Sugarcrm\Support\Helpers\Packager\Instance\MySQL\Files
{
    public function __construct($sugarPath, $archive)
    {
        return parent::__construct($sugarPath, $archive);
    }
}
