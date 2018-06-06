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
     * @param $archive   - the absolute path (including filename) of the archive we're making
     */
    function __construct($sugarPath, $archive, $verbosity)
    {
        $this->sugarPath = $sugarPath;
        $this->archive   = $archive;
        $this->verbosity = $verbosity;

        $this->manifest = json_decode(file_get_contents("{$sugarPath}/sugar_version.json"), true);
        $this->manifest['files'] = array("filesystem");

        return $this;
    }

    /**
     * Packages the files
     */
    function pack()
    {
        $this->addLog('Packing files...', 1);

        $bytestotal = 0;
        $zip = new \ZipArchive();
        $result = $zip->open($this->archive);
        if (TRUE !== $result) {
            throw new \Exception("Could not open {$this->archive}: {$result}", 1);
        }

        $archiveName = basename($this->archive, ".zip");
        $zip->addEmptyDir($archiveName);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->sugarPath,
                \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS
            ),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $fileinfo) {
            $subPathName = sprintf("%s/%s", $archiveName, $iterator->getSubPathname());
            if ($fileinfo->isDir()) {
                $zip->addEmptyDir($subPathName);
            } else {
                $zip->addFile($fileinfo->getPathname(), $subPathName);
                $bytestotal += $fileinfo->getSize();
            }
        }

        $zip->close();

        $this->manifest['files_uncompressed_size'] = $bytestotal;
        return $this->manifest;
    }

    /**
     * Captures an events message
     * @param $message
     */
    function addLog($message, $loglevel)
    {
        if ( $this->verbosity >= $loglevel ) {
            if (php_sapi_name() === 'cli') {
                echo $message . "\n";
            }

            $this->log[] = $message;
        }
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
