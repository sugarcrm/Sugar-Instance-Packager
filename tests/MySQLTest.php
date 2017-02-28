<?php

namespace Sugarcrm\Support\Helpers\Packager\Tests;

/**
 * Class ClientsTest
 * @package Sugarcrm\Support\Helpers\Internal\Tests\API
 * @group support
 */
class ClientsTest extends \PHPUnit_Framework_TestCase {

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
     * @covers ::getProperties
     * @covers ::isClient
     * @group clients
     */
    public function testBackup(){
        $time = time();

        $namespace = '\\Sugarcrm\\Support\\Helpers\\Packager\\Instance\\MySQL\\Packager';

        $packager = new $namespace(
            'sugar',
            'backups',
            $time
        );

        $this->assertTrue(file_exists("backups/{$time}-files.zip"));
        $this->assertTrue(file_exists("backups/{$time}-db.zip"));
        $packager->pack();
    }
}
