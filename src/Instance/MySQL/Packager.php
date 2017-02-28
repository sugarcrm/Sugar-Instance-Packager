<?php

namespace Sugarcrm\Support\Helpers\Packager\Instance\MySQL;

class Packager extends \Sugarcrm\Support\Helpers\Packager\Instance\Abstracted\Packager
{
    /**
     * Packager constructor.
     * @param $sugarPath
     * @param $archivePath
     * @param string $archiveName
     */
    public function __construct($sugarPath, $archivePath, $archiveName = '')
    {
        parent::__construct($sugarPath, $archivePath, $archiveName);
    }

    /**
     * verifies the config is valid
     * @throws \Exception
     */
    public function verifyConfig()
    {
        parent::verifyConfig();

        if (empty($this->config['dbconfig']['db_type'])) {
            throw new \Exception("config dbconfig.db_type is empty.");
        }

        if ($this->config['dbconfig']['db_type'] != 'mysql') {
            throw new \Exception("config dbconfig.db_type is not mysql.");
        }

        $mysqlTest = shell_exec("mysql 2>&1");
        if (strpos($mysqlTest, 'command not found') !== false) {
            throw new \Exception("mysql not found.");
        }

        $mysqldumpTest = shell_exec("mysqldump 2>&1");
        if (strpos($mysqldumpTest, 'command not found') !== false) {
            throw new \Exception("mysqldump not found.");
        }
    }

    /**
     * Packages the files
     */
    public function packFiles()
    {
        $filePacker = new Files($this->sugarPath, $this->archivePath, $this->archiveName);
        $filePacker->pack();
    }

    /**
     * Packages the database
     */
    public function packDatabase()
    {
        $db = new Database($this->archivePath, $this->archiveName, $this->config['dbconfig'], $this->config['dbconfigoption']);
        $db->pack();
    }
}
