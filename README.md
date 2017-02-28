# Helpers for packaging Sugar applications.

[![Build Status](https://travis-ci.com/sugarcrm/Support-Helpers-Packager.svg)](https://travis-ci.com/sugarcrm/Support-Helpers-Packager)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.3-8892BF.svg?style=flat-square)](https://php.net/)

##Executing the backups
```
cd <Support-Helpers-Packer Root>
php execute.php --source="/path/to/sugar" --destination="backups" --name="packageName"
```

## Verify

Run coverage, generate documentation, verify code quality:

`$ npm run all`


## Test

Requires PHPUnit.

`$ phpunit`

Coverage report:

`$ phpunit --coverage-html=coverage/`


## Documentation

Requires PHPDoc.

`$ phpdoc -d ./src -t ./doc/`


## NPM Scripts

Run tests:

`$ npm test`

Run test coverage:

`$ npm run coverage`

Generate documentation:

`$ npm run doc`
