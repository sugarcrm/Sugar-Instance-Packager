<?php

namespace Sugarcrm\Support\Helpers\Packager\Instance\Abstracted;

abstract class Files
{
    protected $source;
    protected $destination;

    /**
     * Files constructor.
     * @param $sourceFolder - the directory to zip
     * @param $destinationFolder - the zip file to archive to
     * @param $archiveName - the name of the archive
     */
    function __construct($sourceFolder, $destinationFolder, $archiveName)
    {
        if (!is_dir($sourceFolder)) {
            throw new \Exception("'{$sourceFolder}' is not a valid directory");
        }

        if (!is_dir($destinationFolder)) {
            throw new \Exception("'{$destinationFolder}' is not a valid directory");
        }

        $this->source = $sourceFolder;
        $this->destination = $destinationFolder . "/{$archiveName}-files.zip";

        return $this;
    }

    /**
     * Packages the files
     */
    function pack()
    {
        $bytestotal = 0;
        $zip = new \ZipArchive();
        $zip->open($this->destination, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->source,
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

    /**
     * generic get function
     * @param $var
     * @return mixed
     */
    function get($var)
    {
        return $this->$var;
    }
}