<?php

namespace Sugarcrm\Support\Helpers\Packager\Instance\Abstracted;

abstract class Database
{
    protected $dbConfig = array();
    protected $dbConfigOptions = array();
    protected $archivePath = '';
    protected $archiveName = '';

    protected $connection = null;
    protected $log = array();
    protected $manifest = array();

    /**
     * Database constructor.
     * @param $archivePath
     * @param $archiveName
     * @param $dbConfig
     * @param $dbConfigOptions
     */
    function __construct($archivePath, $archiveName, $dbConfig, $dbConfigOptions)
    {
        $this->archivePath = $archivePath;
        $this->archiveName = $archiveName;
        $this->dbConfig = $dbConfig;
        $this->dbConfigOptions = $dbConfigOptions;

        if (empty($this->dbConfig['db_port'])) { // '' case
            $this->dbConfig['db_port'] = null;
        }

        $pos = strpos($this->dbConfig['db_host_name'], ':');
        if ($pos !== false) {
            $dbHostName = $this->dbConfig['db_host_name'];
            $this->dbConfig['db_host_name'] = substr($dbHostName, 0, $pos);
            $this->dbConfig['db_port'] = substr($dbHostName, $pos + 1);
        }

        if (ini_get('mysqli.allow_persistent') && $this->dbConfigOptions['persistent']) {
            $this->dbConfig['db_host_name'] = "p:" . $this->dbConfig['db_host_name'];
        }

        if (!isset($this->dbConfig['db_name'])) {
            $this->dbConfig['db_name'] = '';
        }

        if (!isset($this->dbConfig['db_socket'])) {
            $this->dbConfig['db_socket'] = null;
        }

        if (!isset($this->dbConfig['db_client_flags'])) {
            $this->dbConfig['db_client_flags'] = 0;
        }

        if (isset($this->dbConfigOptions['ssl']) && $this->dbConfigOptions['ssl'] == true) {
            if (isset($this->dbConfigOptions['ssl_options']['ssl_ca']) && $this->dbConfigOptions['ssl_options']['ssl_ca']) {
                $this->dbConfigOptions['ssl_options']['ssl_key'] = isset($this->dbConfigOptions['ssl_options']['ssl_key']) ? $this->dbConfigOptions['ssl_options']['ssl_key'] : null;
                $this->dbConfigOptions['ssl_options']['ssl_cert'] = isset($this->dbConfigOptions['ssl_options']['ssl_cert']) ? $this->dbConfigOptions['ssl_options']['ssl_cert'] : null;
                $this->dbConfigOptions['ssl_options']['ssl_ca'] = isset($this->dbConfigOptions['ssl_options']['ssl_ca']) ? $this->dbConfigOptions['ssl_options']['ssl_ca'] : null;
                $this->dbConfigOptions['ssl_options']['ssl_capath'] = isset($this->dbConfigOptions['ssl_options']['ssl_capath']) ? $this->dbConfigOptions['ssl_options']['ssl_capath'] : null;
                $this->dbConfigOptions['ssl_options']['ssl_cipher'] = isset($this->dbConfigOptions['ssl_options']['ssl_cipher']) ? $this->dbConfigOptions['ssl_options']['ssl_cipher'] : null;
            }
        }
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

    /**
     * connects the db
     * @return mixed
     */
    abstract protected function connect();

    /**
     * disconnects the db
     * @return mixed
     */
    abstract protected function disconnect();

    /**
     * packages the db
     * @return mixed
     */
    abstract protected function pack();
}
