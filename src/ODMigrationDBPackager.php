<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2017 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/


namespace Sugarcrm\Support\Helpers\Packagers;

require_once "ZipStreamer-master/src/ZipStreamer.php";
require_once "ZipStreamer-master/src/File.php";
require_once "ZipStreamer-master/src/Output/OutputInterface.php";
require_once "ZipStreamer-master/src/Output/File.php";

class DatabasePackager
{
    private $source;
    private $destination;
    private $archive_name;
    private $dbconfig;
    private $package;

    public function __construct($source, $destination, $archive_name)
    {
        if( empty($source) ) {
            $source = getcwd();
        }
        if( empty($destination) ) {
            $destination = getcwd();
        }
        $this->source = realpath($source);
        $this->destination = realpath($destination);

        if ( false === $this->source || ! is_dir( $this->source ) ) {
            die( sprintf("Specified directory '$zip_dir' for zip file '$zip_archive' extraction does not exist." ) );
        }

        $this->archive_name = $archive_name;

        //pull in the instance's config
        ob_start();
        include($this->source . "/config.php");
        ob_end_clean();

        $this->dbconfig = $sugar_config['dbconfig'];
        unset($sugar_config);

        $mysqldump_connection = sprintf( "-h %s -u %s -p%s %s",
            escapeshellarg($this->dbconfig['db_host_name']),
            escapeshellarg($this->dbconfig['db_user_name']),
            escapeshellarg($this->dbconfig['db_password']),
            escapeshellarg($this->dbconfig['db_name'])
        );
        $$mysql_options  = "--max_allowed_packet=2048M -e -Q --opt";
        $trigger_options = "--no-create-db --no-data --routines";
        $skip_views = "";

        # views need to be dumped separately from the main db contents
        $views = $this->getDbViews();
        if ( $views === false ) {
            echo "could not retrieve list of views";
            return false;
        } else {
            if ( count($views) ) {
                foreach($views as $v) {
                    $skip_views = sprintf("%s--ignore-table=%s ",
                        $skip_views,
                        escapeshellarg( sprintf("%s.%s", $dbname, $v) )
                        );
                }
                $skip_views = rtrim($skip_views);
                $views = implode(" ", array_map('escapeshellarg', $views));
            } else {
                $trigger_options .= " --no-create-info";
                $views = '';
            }

        }

        $this->package = array(
            'db' => array (
                'mysqldump_cmd' => sprintf( "set -o pipefail; mysqldump %s %s %s",
                    $mysql_options,
                    $mysqldump_connection,
                    $skip_views
                    ),
                'filename' => "{$this->archive_name}-db.sql",
                'path' => "{$this->destination}/{$this->archive_name}-db.zip"
            ),
            'triggers' => array (
                'mysqldump_cmd' => sprintf( "set -o pipefail; mysqldump %s %s %s %s | sed -e 's/DEFINER[ ]*=[ ]*[^*]*\*/\*/'",
                    $mysql_options,
                    $trigger_options,
                    $mysqldump_connection,
                    $views
                    ),
                'filename' => "{$this->archive_name}-triggers.sql",
                'path' => "{$this->destination}/{$this->archive_name}-triggers.zip"
            )
        );
    }

    public function pack()
    {
        if ( false === $this->source || ! is_dir( $this->source ) ) {
            die( sprintf("Specified destination directory '%s' does not exist!", $this->source ) );
        }

        $this->dump_db("db");
        $this->dump_db("triggers");

    }

    private function getDbViews()
    {
        $mysqli = new \mysqli(
            $this->dbconfig['db_host_name'],
            $this->dbconfig['db_user_name'],
            $this->dbconfig['db_password'],
            $this->dbconfig['db_name']
        );

        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
            return false;
        }

        $result = $mysqli->query("select table_name from information_schema.views");

        if (! $result) {
            echo "Could not query views from database.";
            return false;
        }

        $views = array();
        while ( $row = $result->fetch_array() ) {
            $views[] = $row[0];
        }

        return $views;
    }

    private function dump_db($package)
    {
	echo $this->package[$package]['mysqldump_cmd'] . "\n";
        $stdout = popen( $this->package[$package]['mysqldump_cmd'] , "r" );

        $output = new \ZipStreamer\Output\File( $this->package[$package]['path'] );
        $zip = new \ZipStreamer\ZipStreamer( $output );
        $zip->add( $this->package[$package]['filename'], $stdout, -1);
        $zip->flush();

        if ( ! file_exists( $this->package[$package]['path'] ) ) {
            throw new \Exception( "could not create package {$this->package[$package]['path']}!", 1 );
            return false;
        }

        $zip = new \ZipArchive();
        $zip->open( $this->package[$package]['path'] );
        $stat = $zip->statName( $this->package[$package]['filename'] );
        $zip->addFromString(
            "manifest.json",
            json_encode( array( 'uncompressed_size' => $stat['size'] ) )
        );
        $zip->close();
    }
}
