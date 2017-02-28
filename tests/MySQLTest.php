<?php

namespace Sugarcrm\Support\Helpers\Packager\Tests;

/**
 * Class MySQLTest
 * @package Sugarcrm\Support\Helpers\Packager\Tests
 * @group support
 */
class MySQLTest extends \PHPUnit_Framework_TestCase {

    /**
     * Initialize objects
     */
    public static function setUpBeforeClass()
    {
    }

    /**
     * Remove objects
     */
    public static function tearDownAfterClass()
    {
    }

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Validates mysql backups
     */
    public function testBackup(){
        $time = time();

        $namespace = '\\Sugarcrm\\Support\\Helpers\\Packager\\Instance\\MySQL\\Packager';

        $packager = new $namespace(
            'sugar',
            'backups',
            $time
        );

        $packager->pack();

        $this->assertTrue(file_exists("backups/{$time}-files.zip"));
        $this->assertTrue(file_exists("backups/{$time}-db.zip"));
    }
}
