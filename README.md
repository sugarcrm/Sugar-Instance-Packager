# Helpers for packaging Sugar applications.

[![Build Status](https://travis-ci.com/sugarcrm/Support-Helpers-Packager.svg?token=ApQ7hyuyE1rftpStfgbN&branch=master)](https://travis-ci.com/sugarcrm/Support-Helpers-Packager)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.4.2-8892BF.svg?style=flat-square)](https://php.net/)

This repo serves as a starting point for packaging Sugar instances. We intend to extend this library in the near future with helpers for packaging Sugar modules, language packs, and themes.

#Packaging Instances
The following sections outline how to work with packaging a Sugar instance.

##Supported Databases
* MySQL

##Installation
```
git clone https://github.com/sugarcrm/Support-Helpers-Packager
cd Support-Helpers-Packager
composer install
```

##Executing a Backup

To execute a backup from the command line, you can use `execute.php`. The setup is as follows
```
cd <Support-Helpers-Packager Root>
php execute.php --source="/path/to/sugar" --destination="backups" --name="packageName"
```

###Command Options
| Option  | Description |
| ------------- | ------------- |
| source  | The path to the Sugar instance folder  |
| destination  | The path to the destination archive folder   |
| name  | Optional. If left empty, backups generated will be saved as `{timestamp}-{archive}.zip`  |
| type  | Optional. Determines the backup strategy. Valid options are `MySQL` and `OnDemand`. If left empty, backups are generated using the `OnDemand` methodology. `OnDemand` creates a `{name}-files.zip`, a `{name}-db.zip`, and a `{name}-triggers.zip` file that separates databse data rows from views and triggers. `MySQL` creates a `{name}-files.zip` and a `{name}-db.zip`.|

## Testing

Requires PHPUnit.
```
#install a Sugar instance to <Support-Helpers-Packager Root>/sugar
vendor/bin/phpunit --group support --verbose
```

#Contributing
Everyone is welcome to be involved by creating or improving packaging scripts. If you would like to contribute, please make sure to review the [CONTRIBUTOR TERMS](CONTRIBUTOR TERMS.pdf). When you update this [README](README.md), please check out the [contribution guidelines](CONTRIBUTING.md) for helpful hints and tips that will make it easier to accept your pull request.

## Contributors
[Jerry Clark](https://github.com/geraldclark)

[Jonathan Wigglesworth](https://github.com/jwigg-sugar)

# Licensed under Apache
© 2017 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.

##Third Party Libraries
* ZipStreamer MIT - PHP zip implementation to stream zips while appending files. 
    * [Github](https://github.com/frizinak/ZipStreamer) 
    * [Packagist](https://packagist.org/packages/frizinak/zip-streamer)
