# Contribution Guidelines
Please ensure your pull request adheres to the following guidelines:

* Please search previous suggestions before making a new one, as yours may be a duplicate.
* Put a link to each library in your pull request ticket, so they're easier to look at.
* There should be no code, e.g. zip files, in the pull request (or the repository itself). This is information and listing purposes only. 
* Use the following format for libraries: \[LIBRARY\]\(LINK\) - DESCRIPTION.
* Keep descriptions short and simple. 
* End all descriptions with a full stop/period.
* Check your spelling and grammar.
* Make sure your text editor is set to remove trailing whitespace.

# Adding New Packagers
* All instance packagers should be located in `./src/Instance/` and contain 3 classes that extend . `\Sugarcrm\Support\Helpers\Packager\Instance\Abstracted\Database`, `\Sugarcrm\Support\Helpers\Packager\Instance\Abstracted\Files`, and `\Sugarcrm\Support\Helpers\Packager\Instance\Abstracted\Packager`


# Unit Tests
PHP unit tests are required for all packaging actions. They should all be located in `./tests/`.

To setup you environment for unit tests, you will need to do the following:

* Clone this repository
* Install composer using `composer install`.
* Install a Sugar instance to the root of this repo named `sugar`
* Create your unit test similar to `./tests/MySQLTest.php` and make sure to add it to the `@group support` in the header comment:
```
/**
 *@group support
 */
```

* Validate the tests by running:

```
cd "/<sugar>/tests/"
phpunit --verbose --debug --group support
```