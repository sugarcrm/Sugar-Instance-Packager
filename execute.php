#!/usr/bin/php
<?php
require __DIR__ . '/vendor/autoload.php';




define('APPNAME', \Sugarcrm\Support\Helpers\Packager\Instance\Abstracted\Packager::APPNAME );
define('VERSION', \Sugarcrm\Support\Helpers\Packager\Instance\Abstracted\Packager::VERSION );
$versionText = sprintf("%s: %s\n", APPNAME, VERSION);

$getOpt = new \GetOpt\GetOpt(
    [
    \GetOpt\Option::create('h',  'help',        \GetOpt\GetOpt::NO_ARGUMENT)
        ->setDescription('Print this help message and exit.'),

    \GetOpt\Option::create('v',  'version',     \GetOpt\GetOpt::NO_ARGUMENT)
        ->setDescription('Print version information and exit.'),

    \GetOpt\Option::create(null, 'name',        \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
        ->setDescription('File name of the package to be created. Defaults to "<AWS Access Key>.<UNIX timestamp>.zip", or "<UNIX timestamp>.zip" if no AWS Access Key is found.')
        ->setArgumentName('package name'),

    \GetOpt\Option::create(null, 'destination', \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
        ->setDescription('Directory to write the package to. Defaults to the current directory.')
        ->setArgument(new \GetOpt\Argument(getcwd(), null, 'directory')),

    \GetOpt\Option::create(null, 'type',        \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
        ->setDescription('Type of package to create. Valid types are "MySQL" or "Cloud". Defaults to "Cloud".')
        ->setArgument(new \GetOpt\Argument('Cloud', null, 'package type')),

    \GetOpt\Option::create(null, 'upload',      \GetOpt\GetOpt::OPTIONAL_ARGUMENT)
        ->setDescription('Upload the package being created OR specify an existing package to be uploaded.')
        ->setArgumentName('path to package'),

    \GetOpt\Option::create(null, 'aws-creds',   \GetOpt\GetOpt::REQUIRED_ARGUMENT)
        ->setDescription('AWS Access Key/Secret pair, separated by ":". If no credentials are provided, attempts to load credentials from environment variables, then "~/.aws/credentials", then "~/.aws/config".')
        ->setArgumentName('key:secret'),

    \GetOpt\Option::create(null, 's3bucket',    \GetOpt\GetOpt::REQUIRED_ARGUMENT)
        ->setDescription('S3 Bucket to upload package to')
        ->setArgumentName('s3bucket')
    ],
    [\GetOpt\GetOpt::SETTING_STRICT_OPERANDS => true]
);


//"operands" are positional arguments
//sugar-path is required, but only if upload !== 1
$getOpt->addOperands(
    [
    \GetOpt\Operand::create('sugar-path',      \GetOpt\Operand::OPTIONAL),
    ]
);

//add description to usage text 
$getOpt->setHelp(
    new \GetOpt\Help(
    ['description' => "Packages a local Sugar installation for upload and import to the SugarCRM Cloud environment.\n\n"
        . "<sugar-path> is required unless an existing package is passed to --upload"
    ]
    )
);

$usage = $versionText . $getOpt->getHelpText();

// process arguments and catch user errors
try {
    $getOpt->process();
} catch (Exception $exception) {
    echo $usage;
    fwrite(STDERR, sprintf("Error: %s \n\n", $exception->getMessage()));
    exit($exception->getCode());
}

$options = $getOpt->getOptions();

//stash operand in $options for convenience
$options['sugar-path'] = $getOpt->getOperand('sugar-path');


if (empty($options['sugar-path']) && !isset($options['upload'])) {
    echo $usage;
    exit(1);
}

if (!empty($options['sugar-path']) && (isset($options['upload']) && 1 !== $options['upload'])) {
    echo $usage;
    fwrite(STDERR, "Error: <sugar-path> and --upload <package> are mutually exclusive\n");
	exit(1);
}

if ($options['help']) {
    echo $usage;
    exit();
}

if ($options['version']) {
    echo $versionText;
    exit();
}

// use AWS creds from the cli first
// if none were given, check for existing credentials using AWS SDK
// no credentials means we can't upload, so leave the package in place and fail loudly
if (!empty($options['aws-creds'])) {
    $options['aws-creds'] = explode(":", $options['aws-creds']);
    $provider = new \Aws\Credentials\Credentials($options['aws-creds'][0], $options['aws-creds'][1]);
} else {
    $provider = \Aws\Credentials\CredentialProvider::defaultProvider();
    $provider = $provider()->wait();
}

$credentials = $provider->toArray();

//set archive name
if (empty($options['name'])) {
    $options['name'] = time() . ".zip";
    if (!empty($credentials['key'])) {
        $options['name'] = "{$credentials['key']}.{$options['name']}";
    }
}


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
            $options['sugar-path'],
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
