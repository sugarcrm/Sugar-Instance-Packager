# Helpers for packaging Sugar applications.

[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.4.2-8892BF.svg?style=flat-square)](https://php.net/)

This repo provides a tool and classes for packaging Sugar instances. We may extend these in the future with helpers for packaging Sugar modules, language packs, and themes.

# Packaging Instances
The following sections outline how to work with packaging a Sugar instance.

## Supported Databases
* MySQL

## Installation
```
git clone https://github.com/sugarcrm/Sugar-Instance-Packager
cd Sugar-Instance-Packager
composer install --no-dev
```

If composer is not already installed, you can install it by following the instructions here: https://getcomposer.org/download/

## Packaging a Sugar instance

To create a package from the command line, you can use `packager.php`. Usage is as follows
```
./packager.php [options] [<sugar-path>]
<sugar-path> is required
```

### Command Options
| Option  | Description |
| ------------- | ------------- |
| -h, --help | Print this help message and exit. |
| -v, --version | Print version information and exit. |
| --name [\<package name>] | File name of the package to be created. Defaults to "\<UNIX timestamp>.zip". |
| --destination [\<directory>] |  Directory to write the package to. Defaults to the current directory. |
| --type [\<package type>] | Type of package to create. Valid types are "MySQL" or "Cloud". Defaults to "Cloud". |

## Testing

Requires PHPUnit.
```
#install a Sugar instance to <Sugar-Instance-Packager Root>/sugar
vendor/bin/phpunit --group support --verbose
```

# Contributing
Everyone is welcome to be involved by creating or improving packaging scripts. If you would like to contribute, please make sure to review the [CONTRIBUTOR TERMS](CONTRIBUTOR%20TERMS.pdf). When you update this [README](README.md), please check out the [contribution guidelines](CONTRIBUTING.md) for helpful hints and tips that will make it easier to accept your pull request.

## Contributors
[Jerry Clark](https://github.com/geraldclark)

[Jonathan Wigglesworth](https://github.com/jwigg-sugar)

# Licensed under Apache
© 2018 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.

## Third Party Libraries
* ZipStreamer MIT - PHP zip implementation to stream zips while appending files. 
    * [Github](https://github.com/frizinak/ZipStreamer) 
    * [Packagist](https://packagist.org/packages/frizinak/zip-streamer)
* AWS SDK PHP
    * [Github](https://github.com/aws/aws-sdk-php)
    * [Packagist](https://packagist.org/packages/aws/aws-sdk-php)
* GetOpt-php
    * [Github](https://github.com/getopt-php/getopt-php)
    * [Packagist](https://packagist.org/packages/ulrichsg/getopt-php)
