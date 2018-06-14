<?php

namespace Sugarcrm\Support\Helpers\Packager\Instance\Abstracted;

abstract class Packager
{
    protected $sugarPath;
    protected $archivePath;
    protected $archiveName;
    protected $archive;
    protected $config;
    protected $log = array();


    const APPNAME = 'Support-Helpers-Packager';
    const VERSION = '1.0.0';

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
    public function get($var)
    {
        return $this->$var;
    }

    /**
     * Packager constructor.
     *
     * @param $sugarPath
     * @param $archivePath
     * @param string $archiveName
     */
    public function __construct($sugarPath, $archivePath, $archiveName = '', $verbosity)
    {
        $this->verbosity = $verbosity;

        //verify path
        if (!is_dir(realpath($sugarPath))) {
            throw new \Exception("'{$sugarPath}' is not a directory", 1);
        }
        $sugarPath = realpath($sugarPath);

        if (!is_file("{$sugarPath}/sugar_version.json")) {
            throw new \Exception("{$sugarPath} does not seem to contain a valid Sugar installation; can't read sugar_version.json", 1);
        }

        //verify archive destination
        $this->sugarPath = $sugarPath;

        if (!is_dir(realpath($archivePath))) {
            throw new \Exception("'{$archivePath}' is not a directory", 1);
        }
        $this->archivePath = realpath($archivePath);
        $this->archiveName = $archiveName;
        $this->archive     = "${archivePath}/{$archiveName}";

        if (is_file($this->archive)) {
            throw new \Exception("'{$this->archive}' already exists", 1);
        }

        $this->loadConfig();

        return $this;
    }

    /**
     * Sets environment for running scripts
     */
    public function setEnvironment()
    {
        ini_set("memory_limit", "-1");
        ini_set("max_execution_time", "0");
    }

    /**
     * returns the sugar configuration
     * @return array
     * @throws \Exception
     */
    public function loadConfig()
    {
        $this->addLog("Loading Sugar config..." , 3);
        $config = $this->sugarPath . '/config.php';
        $config_override = $this->sugarPath . '/config_override.php';

        if (!is_file($config)) {
            throw new \Exception("'{$config}' does not exist. Please make sure you are passing a valid Sugar directory.");
        }

        $sugar_config = array();

        require($config);

        if (is_file($config_override)) {
            require($config_override);
        }

        if (is_array($sugar_config)) {
            ksort($sugar_config);
        }

        $this->config = $sugar_config;
    }

    /**
     * verifies the config is valid
     * @throws \Exception
     */
    public function verifyConfig()
    {
        $this->addLog("Verifying DB config...", 3);
        if (empty($this->config) || !is_array($this->config)) {
            throw new \Exception("Configuration is empty.");
        }

        if (empty($this->config) || !is_array($this->config)) {
            throw new \Exception("Configuration is empty.");
        }

        if (empty($this->config['dbconfig']['db_host_name'])) {
            throw new \Exception("config dbconfig.db_host_name is empty.");
        }

        if (empty($this->config['dbconfig']['db_user_name'])) {
            throw new \Exception("config dbconfig.db_user_name is empty.");
        }

        if (empty($this->config['dbconfig']['db_password'])) {
            throw new \Exception("config dbconfig.db_password is empty.");
        }

        if (empty($this->config['dbconfig']['db_name'])) {
            throw new \Exception("config dbconfig.db_name is empty.");
        }
    }

    /**
     * Packages the files and db
     */
    public function pack()
    {
        $this->verifyConfig();
        $this->setEnvironment();

        /* order is important; DB has to be first because ZipStreamer can't to append to existing zip files */
        $manifest = array_merge_recursive(
            $this->packDatabase(),
            $this->packFiles()
        );

        /* having the manifest inside the package costs us nothing and is a handy backup in a number of scenarios */
        $zip = new \ZipArchive();
        $zip->open($this->archive);
        $this->addLog("Writing manifest to package...", 3);
        $zip->addFromString("manifest.json", json_encode($manifest));
        $zip->close();

        $this->addLog("Packed instance '{$this->sugarPath}' to package '{$this->archive}'", 3);

        return $manifest;
    }

    /**
     * packages sugar files
     */
    abstract function packFiles();

    /**
     * packages sugar database
     */
    abstract function packDatabase();
}
