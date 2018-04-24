<?php

namespace Sugarcrm\Support\Helpers\Packager\Instance\Abstracted;

abstract class Files
{
    protected $sugarPath;
    protected $destination;
    protected $log = array();
    protected $manifest = array();

    /**
     * Files constructor.
     * @param $sugarPath - the directory to be zipped
     * @param $destinationFolder - the zip file to archive to
     * @param $archiveName - the name of the archive
     */
    function __construct($sugarPath, $destinationFolder, $archiveName)
    {

        $this->manifest = json_decode(file_get_contents("{$sugarPath}/sugar_version.json"), true);
	$this->manifest['files'] = array("filesystem");

        return $this;
    }

    /**
     * Packages the files
     */
    function pack()
    {
        $this->addLog('Packing files...');

        $bytestotal = 0;
        $zip = new \ZipArchive();
        $zip->open($this->destination, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->sugarPath,
                \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS
            ),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($it as $fileinfo) {
            $subPathName = $it->getSubPathname();
            if ($fileinfo->isDir()) {
                $zip->addEmptyDir($subPathName);
            } else {
                //$this->addLog('Adding ' . $fileinfo->getPathname());
                $zip->addFile($fileinfo->getPathname(), $subPathName);
                $bytestotal += $fileinfo->getSize();
            }
        }

	$this->addLog('Closing zip...');
	$zip->close();

        $this->manifest['files_uncompressed_size'] = $bytestotal;
        return $this->manifest;
    }

    /**
     * Captures an events message
     * @param $message
     */
    function addLog($message)
    {
        if (php_sapi_name() === 'cli') {
            echo $message . "\n";
        }

        $this->log[] = $message;
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
