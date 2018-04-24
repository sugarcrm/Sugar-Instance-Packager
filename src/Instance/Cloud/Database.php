<?php

namespace Sugarcrm\Support\Helpers\Packager\Instance\Cloud;

class Database extends \Sugarcrm\Support\Helpers\Packager\Instance\MySQL\Database
{
    /**
     * Database constructor.
     * @param $archivePath
     * @param $archiveName
     * @param $dbConfig
     * @param $dbConfigOptions
     */
    function __construct($archive, $dbConfig, $dbConfigOptions)
    {
        parent::__construct($archive, $dbConfig, $dbConfigOptions);
    }

    /**
     * Packages the database
     */
    function pack()
    {
        $this->addLog('Packing database...');

        $trigger_options = " --no-create-db --no-data --routines";
        $skip_views = "";

        # views need to be dumped separately from the main db contents
        $views = $this->getViews();

        if (count($views)) {
            foreach ($views as $v) {
                $skip_views = sprintf("%s--ignore-table=%s ",
                    $skip_views,
                    escapeshellarg(sprintf("%s.%s", $this->dbConfig['db_name'], $v))
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
                'mysqldump_cmd' => $this->getDBCommand($skip_views . " 2> %s"),
                'filename' => basename($this->archive, ".zip") . "-db.sql",
            ),
            'triggers' => array(
                'mysqldump_cmd' => $this->getDBCommand($trigger_options . " " . $views . " 2> %s | sed -e 's/DEFINER[ ]*=[ ]*[^*]*\*/\*/'"),
                'filename' => basename($this->archive, ".zip") . "-triggers.sql",
            )
        );

        $this->execute();
        return $this->manifest;
    }
}
