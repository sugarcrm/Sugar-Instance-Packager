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

    try {
        $packager = new $namespace(
            $options['source'],
            $options['destination'],
            $options['name']
        );

        $manifest = $packager->pack();
    } catch (Exception $e) {
        printf("Error: %s \n", $e->getMessage());
        exit($e->getCode());
    }
    $package = "${options['destination']}/${options['name']}";
}

//no point in trying to upload if we weren't asked to
if (isset($options['upload'])) {

    if (empty($credentials)) {
        print "no AWS credentials found, could not upload package\n";
        exit(1);
    }

    $result = false;
    try{
        echo "Connecting to S3 bucket...\n";
        $s3Client = new Aws\S3\S3Client([
            'version' => 'latest',
            'region'  => 'us-east-1',
            'credentials' => $credentials
        ]);

        echo "Uploading package...\n";
        $result = $s3Client->putObject([
            'Bucket'     => $options['s3bucket'],
            'Key'        => $options['name'],
            'SourceFile' => $package,
        ]);
    } catch (S3Exception $e) {
        echo $e->getMessage() . "\n";
    }
    if ($result) {
        printf( "Uploaded %s to S3 \n\tETag '%s' \n\texpires on %s\n", "{$options['destination']}/{$options['name']}", $result['ETag'], $result['Expiration']);
    }
}
