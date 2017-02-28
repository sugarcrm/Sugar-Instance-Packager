# Helpers for packaging Sugar applications.

[![Build Status](https://travis-ci.com/sugarcrm/Support-Helpers-Packager.svg?token=ApQ7hyuyE1rftpStfgbN&branch=master)](https://travis-ci.com/sugarcrm/Support-Helpers-Packager)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.4.2-8892BF.svg?style=flat-square)](https://php.net/)

##Executing A Backup

To execute a backup, you can use `execute.php`. The setup is as follows
```
cd <Support-Helpers-Packer Root>
php execute.php --source="/path/to/sugar" --destination="backups" --name="packageName"
```
|Option|Descrition|
|-|-|
|source|blah|

## Verify

Run coverage, generate documentation, verify code quality:

`$ npm run all`


## Testing

Requires PHPUnit.
`$ vendor/bin/phpunit --group support --verbose`

