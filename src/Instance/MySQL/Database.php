<?php

namespace Sugarcrm\Support\Helpers\Packager\Instance\MySQL;

class Database extends \Sugarcrm\Support\Helpers\Packager\Instance\Abstracted\Database
{
    protected $package = array();

    /**
     * Database constructor.
     * @param $archivePath
     * @param $archiveName
     * @param $dbConfig
     * @param $dbConfigOptions
     */
    function __construct($archivePath, $archiveName, $dbConfig, $dbConfigOptions)
    {
        parent::__construct($archivePath, $archiveName, $dbConfig, $dbConfigOptions);

        if (isset($this->dbConfigOptions['ssl'])) {
            if (isset($this->dbConfigOptions['ssl_options']['ssl_ca']) && $this->dbConfigOptions['ssl_options']['ssl_ca']) {
                //already set
            } else {
                $this->dbConfig['db_client_flags'] = $this->dbConfig['db_client_flags'] | MYSQLI_CLIENT_SSL;
            }
        }
    }

    /**
     * Connects to mysql
     * @return bool
     * @throws \Exception
     */
    function connect()
    {
        if (is_object($this->connection)) {
            return;
        }

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $this->connection = new \mysqli();

        if (isset($this->dbConfigOptions['ssl']) && isset($this->dbConfigOptions['ssl_options']['ssl_ca']) && $this->dbConfigOptions['ssl_options']['ssl_ca']) {
            $this->connection->ssl_set(
                $this->dbConfigOptions['ssl_options']['ssl_key'],
                $this->dbConfigOptions['ssl_options']['ssl_cert'],
                $this->dbConfigOptions['ssl_options']['ssl_ca'],
                $this->dbConfigOptions['ssl_options']['ssl_capath'],
                $this->dbConfigOptions['ssl_options']['ssl_cipher']
            );
        }

        $this->connection->real_connect(
            $this->dbConfig['db_host_name'],
            $this->dbConfig['db_user_name'],
            $this->dbConfig['db_password'],
            $this->dbConfig['db_name'],
            $this->dbConfig['db_port'],
            $this->dbConfig['db_socket'],
            $this->dbConfig['db_client_flags']
        );

        if ($this->connection->connect_errno) {
            throw new \Exception("Failed to connect to MySQL: (" . $this->connection->connect_errno . ") " . $this->connection->connect_erro);
        }
    }

    /**
     * Disconnects from mysql
     */
    function disconnect()
    {
        if (!is_null($this->connection) && $this->connection !== false) {
            $this->connection->close();
        }

        $this->connection = null;
    }

    /**
     * returns all the triggers on the database
     * @return array
     * @throws \Exception
     */
    function getTriggers()
    {
        $this->connect();

        $result = $this->connection->query("SHOW TRIGGERS IN " . $this->dbConfig['db_name']);

        if (!$result) {
            throw new \Exception("Could not query triggers from database.");
        }

        $this->disconnect();

        $triggers = array();
        while ($row = $result->fetch_array()) {
            $triggers[] = $row[0];
        }

        return $triggers;
    }

    /**
     * returns all the views on the database
     * @return array
     * @throws \Exception
     */
    function getViews()
    {
        $this->connect();

        $result = $this->connection->query("SHOW FULL TABLES IN " . $this->dbConfig['db_name'] . " WHERE TABLE_TYPE LIKE 'VIEW'");

        if (!$result) {
            throw new \Exception("Could not query views from database.");
        }

        $this->disconnect();

        $views = array();
        while ($row = $result->fetch_array()) {
            $views[] = $row[0];
        }

        return $views;
    }

    /**
     * Packages the database
     */
    function pack()
    {
        $this->addLog('Packing database...');

        $this->package = array(
            'db' => array(
                'mysqldump_cmd' => $this->getDBCommand(),
                'filename' => "{$this->archiveName}-db.sql",
                'path' => "{$this->archivePath}/{$this->archiveName}-db.zip"
            )
        );

        $this->execute();
    }

    /**
     * Executes the packaging of the instance
     * @throws \Exception
     */
    function execute()
    {
        foreach ($this->package as $package) {

            $this->addLog($package['mysqldump_cmd']);

            $stdout = popen($package['mysqldump_cmd'], "r");
            $output = new \ZipStreamer\Output\File($package['path']);
            $zip = new \ZipStreamer\ZipStreamer($output);
            $zip->add($package['filename'], $stdout, -1);
            $zip->flush();

            if (!file_exists($package['path'])) {
                throw new \Exception("could not create package {$package['path']}!", 1);
            }

            $zip = new \ZipArchive();
            $zip->open($package['path']);
            $stat = $zip->statName($package['filename']);
            $zip->addFromString(
                "manifest.json",
                json_encode(array('uncompressed_size' => $stat['size']))
            );
            $zip->close();
        }
    }

    /**
     * Generates the generic db command
     *
     * @param string $append
     * @return string
     */
    function getDBCommand($append = '')
    {
        //set @@global.show_compatibility_56=ON;
        $command = "mysqldump";

        $command .= " --set-gtid-purged=OFF --max_allowed_packet=1024M -e -Q --opt";

        if (isset($this->dbConfigOptions['ssl']) && $this->dbConfigOptions['ssl'] == true && isset($this->dbConfigOptions['ssl_options']['ssl_ca']) && $this->dbConfigOptions['ssl_options']['ssl_ca']) {
            $command .= " --ssl-mode=REQUIRED";
            foreach ($this->dbConfigOptions as $key => $value) {
                if (substr($key, 0, 4) == 'ssl_' && !is_null($value)) {
                    $command .= ' --' . str_replace('ssl_', 'ssl-', $key) . '=' . $value;
                }
            }
        }

        if (!empty($this->dbConfig['db_socket'])) {
            $command .= " --socket=" . $this->dbConfig['db_socket'];
        }

        $command .= sprintf(" -h %s -u %s -p%s %s",
            escapeshellarg($this->dbConfig['db_host_name']),
            escapeshellarg($this->dbConfig['db_user_name']),
            escapeshellarg($this->dbConfig['db_password']),
            escapeshellarg($this->dbConfig['db_name'])
        );

        if (!empty($this->dbConfig['db_port'])) {
            $command .= " -P " . $this->dbConfig['db_port'];
        }

        $command .= $append;

        return $command;
    }
}