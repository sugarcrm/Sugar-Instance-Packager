#!/usr/bin/php
<?php

require __DIR__ . '/vendor/autoload.php';

$longopts = array("source:", "destination:", "name:", "type::");

$options = getopt('', $longopts);

if (empty($options['type'])) {
    $options['type'] = 'OnDemand';
}

define('APPNAME', \Sugarcrm\Support\Helpers\Packager\Instance\Abstracted\Packager::APPNAME );
define('VERSION', \Sugarcrm\Support\Helpers\Packager\Instance\Abstracted\Packager::VERSION );


$namespace = '\\Sugarcrm\\Support\\Helpers\\Packager\\Instance\\' . $options['type'] . '\\Packager';

$packager = new $namespace(
    $options['source'],
    $options['destination'],
    $options['name']
);

$packager->pack();
