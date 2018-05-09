<?php

require_once __DIR__.'/../vendor/autoload.php';

$classLoader = new \Composer\Autoload\ClassLoader();
$classLoader->addPsr4("Sugarcrm\\Support\\Helpers\\Packager\\Tests\\Helpers", __DIR__, true);
$classLoader->register();
