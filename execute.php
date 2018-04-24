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



);

//allow uploading to be completed separate from packing
//if upload isn't empty, then that's the package we're trying to upload
//otherwise, create a new package
if (!empty($options['upload'])) {
    $package = $options['upload'];
    if (!is_file($package)) {
        print "Could not read package ${package}; make sure it exists and its permissions allow reading\n";
    }
} else {
    $namespace = '\\Sugarcrm\\Support\\Helpers\\Packager\\Instance\\' . $options['type'] . '\\Packager';

    $packager = new $namespace(
        $options['source'],
        $options['destination'],
        $options['name']
    );

    $packager->pack();
}

