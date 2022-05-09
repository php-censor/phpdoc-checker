[![Minimum PHP version: 7.4.0](https://img.shields.io/badge/php-7.4.0%2B-blue.svg?label=PHP)](https://packagist.org/packages/php-censor/phpdoc-checker)
[![Actions](https://github.com/php-censor/phpdoc-checker/actions/workflows/ci.yaml/badge.svg)](https://github.com/php-censor/phpdoc-checker/actions)
[![PHP Censor](http://ci.php-censor.info/build-status/image/16?branch=master&label=PHP%20Censor)](http://ci.php-censor.info/build-status/view/16?branch=master)
[![Codecov](https://codecov.io/gh/php-censor/phpdoc-checker/branch/master/graph/badge.svg)](https://codecov.io/gh/php-censor/phpdoc-checker)
[![Latest Version](https://img.shields.io/packagist/v/php-censor/phpdoc-checker.svg?label=Version)](https://packagist.org/packages/php-censor/phpdoc-checker)
[![Total downloads](https://img.shields.io/packagist/dt/php-censor/phpdoc-checker.svg?label=Downloads)](https://packagist.org/packages/php-censor/phpdoc-checker)
[![License](https://img.shields.io/packagist/l/php-censor/phpdoc-checker.svg?label=License)](https://packagist.org/packages/php-censor/phpdoc-checker)

# PHPDoc Checker

Check PHP files within a directory for appropriate use of PHPDocs (Docblocks). PHPDoc Checker is fork of 
[PHP DocBlock Checker](https://github.com/Block8/php-docblock-checker). 

## Installation
**Composer**:<br>

```bash
composer require php-censor/phpdoc-checker
```

## Building Phar Package

```bash
box.phar compile
```

## Usage

```bash
vendor/bin/phpdoc-checker {params}
```

### Parameters

Short | Long | Description
------------ | ------------- | -----------
-h | --help | Display help message.
-x | --exclude=EXCLUDE | Files and directories to exclude.
-d | --directory=DIRECTORY | Directory to scan. [default: "./"]
-f | --files=FILES | Files to scan.
none | --skip-classes | Don't check classes for docblocks.
none | --skip-methods | Don't check methods for docblocks.
none | --skip-signatures | Don't check docblocks against method signatures.
-j | --json | Output JSON instead of a log.
-l | --files-per-line=FILES-PER-LINE | Number of files per line in progress [default: 50]
-w | --fail-on-warnings | Consider the check failed if any warnings are produced.
-i | --info-only | Information-only mode, just show summary.
-q | --quiet | Do not output any message.
-V | --version | Display this application version.
none | --ansi | Force ANSI output.
none | --no-ansi | Disable ANSI output.
-n | --no-interaction | Do not ask any interactive question.
-v -vv -vvv | --verbose | Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug.

## Unit tests

Phpunit tests:

```bash
vendor/bin/phpunit --configuration=phpunit.xml.dist --coverage-text --coverage-html=tests/var/coverage
```

## License

PHPDoc Checker is open source software licensed under the [BSD-2-Clause license](LICENSE).
