# Helpers for packaging Sugar applications.

[![Build Status](https://travis-ci.com/sugarcrm/Support-Helpers-Packager.svg?token=ApQ7hyuyE1rftpStfgbN&branch=master)](https://travis-ci.com/sugarcrm/Support-Helpers-Packager)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.4.2-8892BF.svg?style=flat-square)](https://php.net/)

##Executing A Backup

To execute a backup, you can use `execute.php`. The setup is as follows
```
cd <Support-Helpers-Packer Root>
php execute.php --source="/path/to/sugar" --destination="backups" --name="packageName"
```

###Command Options
| Option  | Description |
| ------------- | ------------- |
| source  | Content Cell  |
| destination  | Content Cell  |
| name  | Optional. If left empty, backups generated will be created as `{timestamp}-{type}.zip`  |
| type  | Optional. Determines the backup strategy. Valid options are `MySQL` and `OnDemand`. If left empty, backups are generated using the `OnDemand` methodology. `OnDemand` creates a `{name}-files.zip`, `{name}-db.zip`, and `{name}-triggers.zip` file that separates databse data rows from views and triggers. `MySQL` creates a `{name}-files.zip` and `{name}-db.zip`.|

## Testing

Requires PHPUnit.
`$ vendor/bin/phpunit --group support --verbose`
