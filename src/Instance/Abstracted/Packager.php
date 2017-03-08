<?php

namespace Sugarcrm\Support\Helpers\Packager\Instance\Abstracted;

abstract class Packager
{
    protected $sugarPath;
    protected $archivePath;
    protected $archiveName;
    protected $config;
    protected $log = array();

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
    public function __construct($sugarPath, $archivePath, $archiveName = '')
    {
        if (empty($sugarPath)) {
            $sugarPath = getcwd();
        }

        //verify path
        $sugarPath = rtrim($sugarPath, '/');
        if (!is_dir($sugarPath)) {
            throw new \Exception("'{$sugarPath}' is not a Sugar directory");
        }
        $this->sugarPath = $sugarPath;

        //verify archive destination
        $archivePath = rtrim($archivePath, '/');
        if (!is_dir($archivePath)) {
            throw new \Exception("'{$archivePath}' is not a directory");
        }
        $this->archivePath = $archivePath;

        //set archive name
        $archiveName = rtrim($archiveName, ".zip");
        if (empty($archiveName)) {
            $archiveName = time();
        }

        $this->archiveName = $archiveName;

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
        $this->packDatabase();
        $this->packFiles();
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
