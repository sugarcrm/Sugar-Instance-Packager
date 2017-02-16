<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2017 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

namespace Sugarcrm\Support\Helpers\Packagers;

class FilePackager
{
    private $source;
    private $destination;
    private $archive_name;

    public function __construct($source, $destination, $archive_name)
    {
        if ( empty($source) ) {
            $source = getcwd();
        }
        if ( empty($destination) ) {
            $destination = getcwd();
        }
        $this->source = realpath($source);
        $this->destination = realpath($destination);

        $this->archive_name = $archive_name;
    }

    /*
     * Runs the packager on the root directory
     */
    public function pack()
    {

        if ( false === $this->source || ! is_dir( $this->source ) ) {
            die( sprintf("Specified destination directory '%s' does not exist!", $this->source ) );
        }

        //Begin packing {$source} to {$destination}
        ini_set( "memory_limit", "-1" );
        ini_set( "max_execution_time", "0" );
        $this->zip_dir( $this->source, sprintf("%s/%s-files.zip",  $this->destination, $this->archive_name) );

    }

    private function zip_dir( $source, $destination )
    {
        $bytestotal = 0;

        $zip = new \ZipArchive();

        $zip->open($destination, \ZipArchive::CREATE|\ZipArchive::OVERWRITE);

        /** @var RecursiveIteratorIterator|RecursiveDirectoryIterator $it */
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $source,
                \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS
            ),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($it as $fileinfo) {
            $subPathName = $it->getSubPathname();
            if ($fileinfo->isDir()) {
                $zip->addEmptyDir($subPathName);
            } else {
                $zip->addFile($fileinfo->getPathname(), $subPathName);
                $bytestotal += $fileinfo->getSize();
            }
        }

        $zip->addFromString(
            "manifest.json",
            json_encode(array('uncompressed_size' => $bytestotal))
        );
        $zip->close();
    }

}
