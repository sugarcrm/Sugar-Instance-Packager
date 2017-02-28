<?php

namespace Sugarcrm\Support\Helpers\Packager\Instance\OnDemand;

class Database extends \Sugarcrm\Support\Helpers\Packager\Instance\MySQL\Database
{
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
    }

    /**
     * Packages the database
     */
    function pack()
    {
        $trigger_options = " --no-create-db --no-data --routines";
        $skip_views = "";

        # views need to be dumped separately from the main db contents
        $views = $this->getViews();

        if (count($views)) {
            foreach ($views as $v) {
                $skip_views = sprintf("%s--ignore-table=%s ",
                    $skip_views,
                    escapeshellarg(sprintf("%s.%s", $this->dbconfig['db_name'], $v))
                );
            }
            $skip_views = rtrim($skip_views);
            $views = implode(" ", array_map('escapeshellarg', $views));
        } else {
            $trigger_options .= " --no-create-info";
            $views = '';
        }

        $this->package = array(
            'db' => array(
                'mysqldump_cmd' => $this->getDBCommand($skip_views),
                'filename' => "{$this->archiveName}-db.sql",
                'path' => "{$this->archivePath}/{$this->archiveName}-db.zip"
            ),
            'triggers' => array(
                'mysqldump_cmd' => $this->getDBCommand($trigger_options . " " . $views . " | sed -e 's/DEFINER[ ]*=[ ]*[^*]*\*/\*/'"),
                'filename' => "{$this->archiveName}-triggers.sql",
                'path' => "{$this->archivePath}/{$this->archiveName}-triggers.zip"
            )
        );

        $this->execute();
    }
}
