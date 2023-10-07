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
