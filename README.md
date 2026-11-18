<div aria-hidden="true">

[![CI](https://github.com/contactjavas/wpcs/actions/workflows/ci.yml/badge.svg)](https://github.com/contactjavas/wpcs/actions/workflows/ci.yml)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.4-8892BF.svg)](https://www.php.net/supported-versions.php)
[![Tested on PHP 8.4 to 8.5](https://img.shields.io/badge/tested%20on-PHP%208.4%20|%208.5-green.svg)](https://github.com/contactjavas/wpcs/actions/workflows/ci.yml)
[![PHP_CodeSniffer 4](https://img.shields.io/badge/PHP__CodeSniffer-4.x-blue.svg)](https://github.com/PHPCSStandards/PHP_CodeSniffer)
[![License: MIT](https://img.shields.io/github/license/contactjavas/wpcs)](https://github.com/contactjavas/wpcs/blob/develop/LICENSE)

</div>


# WordPress Coding Standards for PHP_CodeSniffer (modern fork)

* [Introduction](#introduction)
    + [Differences from upstream WordPressCS](#differences-from-upstream-wordpresscs)
* [Minimum Requirements](#minimum-requirements)
* [Installation](#installation)
    + [Composer Project-based Installation](#composer-project-based-installation)
    + [Composer Global Installation](#composer-global-installation)
    + [Updating your WordPressCS install to a newer version](#updating-your-wordpresscs-install-to-a-newer-version)
    + [Using your WordPressCS install](#using-your-wordpresscs-install)
* [Rulesets](#rulesets)
    + [Standards subsets](#standards-subsets)
    + [Using a custom ruleset](#using-a-custom-ruleset)
    + [Customizing sniff behavior](#customizing-sniff-behavior)
    + [Recommended additional rulesets](#recommended-additional-rulesets)
* [How to use](#how-to-use)
    + [Command line](#command-line)
    + [Using PHPCS and WordPressCS from within your IDE](#using-phpcs-and-wordpresscs-from-within-your-ide)
* [Running your code through WordPressCS automatically using Continuous Integration tools](#running-your-code-through-wordpresscs-automatically-using-continuous-integration-tools)
* [Fixing errors or ignoring them](#fixing-errors-or-ignoring-them)
    + [Tools shipped with WordPressCS](#tools-shipped-with-wordpresscs)
* [Contributing](#contributing)
* [Funding](#funding)
* [License](#license)

---

## Introduction

This project is a collection of [PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer) rules (sniffs) to validate code developed for WordPress. It ensures code quality and adherence to coding conventions, especially the official [WordPress Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/).

`contactjavas/wpcs` is a fork of [WordPressCS](https://github.com/WordPress/WordPress-Coding-Standards) for modern PHP. It is used by [Modern WPCS Ruleset](https://github.com/contactjavas/modern-wpcs-ruleset), and fixes from upstream WordPressCS are merged in regularly.

### Differences from upstream WordPressCS

* Requires PHP 8.4+ and PHP_CodeSniffer 4 (upstream: PHP 7.2+, PHP_CodeSniffer 3).
* Understands PHP_CodeSniffer 4's tokenization of names (`\foo()`, `My\foo()`, `namespace\foo()` are single tokens).
* The code base uses `declare(strict_types=1)` and native parameter, return and property types throughout.
  If you **extend** the abstract sniffs (`WordPressCS\WordPress\Abstract*Sniff`, `Sniff`) or the helper traits in
  your own standard, your overriding methods need matching signatures. Methods that may return a stack pointer
  (`process_token()`, `process_matched_token()`, `process_parameters()`, …) return `?int`.
* Sniff names, error codes and properties are unchanged, so existing `phpcs.xml` files keep working.

## Minimum Requirements

This package requires:
* PHP 8.4 or higher with the following extensions enabled:
    - [Filter](https://www.php.net/book.filter)
    - [libxml](https://www.php.net/book.libxml)
    - [Tokenizer](https://www.php.net/book.tokenizer)
    - [XMLReader](https://www.php.net/book.xmlreader)
* [PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer) 4.0.1 or higher (installed automatically)
* [Composer](https://getcomposer.org/)

For the best results, it is recommended to also ensure the following additional PHP extensions are enabled:
- [iconv](https://www.php.net/book.iconv)
- [Multibyte String](https://www.php.net/book.mbstring)

## Installation

Installation via Composer is the only supported type of installation. This fork is not published on Packagist, so add its GitHub repository to your `composer.json` first.

[Composer](https://getcomposer.org/) will automatically install the project dependencies and register the rulesets from WordPressCS and other external standards with PHP_CodeSniffer using the [Composer PHPCS plugin](https://github.com/PHPCSStandards/composer-installer).

> Most users will want [Modern WPCS Ruleset](https://github.com/contactjavas/modern-wpcs-ruleset) instead, which installs this package for you.

### Composer Project-based Installation

Run the following from the root of your project:
```bash
composer config repositories.wpcs vcs https://github.com/contactjavas/wpcs
composer config allow-plugins.dealerdirect/phpcodesniffer-composer-installer true
composer require --dev contactjavas/wpcs:"^5.0"
```

### Composer Global Installation

Alternatively, you may want to install this standard globally:
```bash
composer global config repositories.wpcs vcs https://github.com/contactjavas/wpcs
composer global config allow-plugins.dealerdirect/phpcodesniffer-composer-installer true
composer global require --dev contactjavas/wpcs:"^5.0"
```

### Updating your WordPressCS install to a newer version

If you installed WordPressCS using either of the above commands, you can upgrade to a newer version as follows:
```bash
# Project local install
composer update contactjavas/wpcs --with-dependencies

# Global install
composer global update contactjavas/wpcs --with-dependencies
```

### Using your WordPressCS install

Once you have installed WordPressCS using either of the above commands, use it as follows:
```bash
# Project local install
vendor/bin/phpcs -ps . --standard=WordPress

# Global install
%USER_DIRECTORY%/Composer/vendor/bin/phpcs -ps . --standard=WordPress
```

> **Pro-tip**: For the convenience of using `phpcs` as a global command, use the _Global install_ method and add the path to the `%USER_DIRECTORY%/Composer/vendor/bin` directory to the `PATH` environment variable for your operating system.

##  Rulesets

### Standards subsets

The project encompasses a super-set of the sniffs that the WordPress community may need. If you use the `WordPress` standard you will get all the checks.

You can use the following as standard names when invoking `phpcs` to select sniffs, fitting your needs:

* `WordPress` - complete set with all of the sniffs in the project
  - `WordPress-Core` - main ruleset for [WordPress core coding standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
  - `WordPress-Docs` - additional ruleset for [WordPress inline documentation standards](https://developer.wordpress.org/coding-standards/inline-documentation-standards/php/)
  - `WordPress-Extra` - extended ruleset with recommended best practices, not sufficiently covered in the WordPress core coding standards
    - includes `WordPress-Core`

### Using a custom ruleset

If you need to further customize the selection of sniffs for your project - you can create a custom ruleset file.

When you name this file either `.phpcs.xml`, `phpcs.xml`, `.phpcs.xml.dist` or `phpcs.xml.dist`, PHP_CodeSniffer will automatically locate it as long as it is placed in the directory from which you run the CodeSniffer or in a directory above it. If you follow these naming conventions you don't have to supply a `--standard` CLI argument.

For more info, read about [using a default configuration file](https://github.com/PHPCSStandards/PHP_CodeSniffer/wiki/Advanced-Usage#using-a-default-configuration-file). See also the provided WordPressCS [`phpcs.xml.dist.sample`](phpcs.xml.dist.sample) file and the [fully annotated example ruleset](https://github.com/PHPCSStandards/PHP_CodeSniffer/wiki/Annotated-ruleset.xml) in the PHP_CodeSniffer documentation.

### Customizing sniff behavior

The WordPress Coding Standard contains a number of sniffs which are configurable. This means that you can turn parts of the sniff on or off, or change the behavior by setting a property for the sniff in your custom `[.]phpcs.xml[.dist]` file.

You can find a complete list of all the properties you can change for the WordPressCS sniffs in the [wiki](https://github.com/WordPress/WordPress-Coding-Standards/wiki/Customizable-sniff-properties).

WordPressCS also uses sniffs from PHPCSExtra and from PHP_CodeSniffer itself.
The [README for PHPCSExtra](https://github.com/PHPCSStandards/PHPCSExtra) contains information on the properties which can be set for the sniff from PHPCSExtra.
Information on custom properties which can be set for sniffs from PHP_CodeSniffer can be found in the [PHP_CodeSniffer wiki](https://github.com/PHPCSStandards/PHP_CodeSniffer/wiki/Customisable-Sniff-Properties).


### Recommended additional rulesets

#### PHPCompatibility

The [PHPCompatibility](https://github.com/PHPCompatibility/PHPCompatibility) ruleset and its subset [PHPCompatibilityWP](https://github.com/PHPCompatibility/PHPCompatibilityWP) come highly recommended.
The [PHPCompatibility](https://github.com/PHPCompatibility/PHPCompatibility) sniffs are designed to analyze your code for cross-version PHP compatibility.

The [PHPCompatibilityWP](https://github.com/PHPCompatibility/PHPCompatibilityWP) ruleset is based on PHPCompatibility, but specifically crafted to prevent false positives for projects which expect to run within the context of WordPress, i.e. core, plugins and themes.

Install either as a separate ruleset and run it separately against your code or add it to your custom ruleset, like so:
```xml
<config name="testVersion" value="7.2-"/>
<rule ref="PHPCompatibilityWP">
    <include-pattern>*\.php$</include-pattern>
</rule>
```

Whichever way you run it, do make sure you set the `testVersion` to run the sniffs against. The `testVersion` determines for which PHP versions you will receive compatibility information. The recommended setting for this at this moment is  `7.2-` to support the last three WordPress releases.

For more information about setting the `testVersion`, see:
* [PHPCompatibility: Sniffing your code for compatibility with specific PHP version(s)](https://github.com/PHPCompatibility/PHPCompatibility#sniffing-your-code-for-compatibility-with-specific-php-versions)
* [PHPCompatibility: Using a custom ruleset](https://github.com/PHPCompatibility/PHPCompatibility#using-a-custom-ruleset)

#### VariableAnalysis

For some additional checks around (undefined/unused) variables, the [`VariableAnalysis`](https://github.com/sirbrillig/phpcs-variable-analysis/) standard is a handy addition.

#### VIP Coding Standards

For those projects which deploy to the WordPress VIP platform, it is recommended to also use the [official WordPress VIP coding standards](https://github.com/Automattic/VIP-Coding-Standards) ruleset.


## How to use

### Command line

Run the `phpcs` command line tool on a given file or directory, for example:
```bash
vendor/bin/phpcs --standard=WordPress wp-load.php
```

Will result in following output:
```
--------------------------------------------------------------------------------
FOUND 6 ERRORS AND 4 WARNINGS AFFECTING 5 LINES
--------------------------------------------------------------------------------
  36 | WARNING | error_reporting() can lead to full path disclosure.
  36 | WARNING | error_reporting() found. Changing configuration values at
     |         | runtime is strongly discouraged.
  52 | WARNING | Silencing errors is strongly discouraged. Use proper error
     |         | checking instead. Found: @file_exists( dirname(...
  52 | WARNING | Silencing errors is strongly discouraged. Use proper error
     |         | checking instead. Found: @file_exists( dirname(...
  75 | ERROR   | Overriding WordPress globals is prohibited. Found assignment
     |         | to $path
  78 | ERROR   | Detected usage of a possibly undefined superglobal array
     |         | index: $_SERVER['REQUEST_URI']. Use isset() or empty() to
     |         | check the index exists before using it
  78 | ERROR   | $_SERVER['REQUEST_URI'] not unslashed before sanitization. Use
     |         | wp_unslash() or similar
  78 | ERROR   | Detected usage of a non-sanitized input variable:
     |         | $_SERVER['REQUEST_URI']
 104 | ERROR   | All output should be run through an escaping function (see the
     |         | Security sections in the WordPress Developer Handbooks), found
     |         | '$die'.
 104 | ERROR   | All output should be run through an escaping function (see the
     |         | Security sections in the WordPress Developer Handbooks), found
     |         | '__'.
--------------------------------------------------------------------------------
```

### Using PHPCS and WordPressCS from within your IDE

The [wiki](https://github.com/WordPress/WordPress-Coding-Standards/wiki) contains links to various in- and external tutorials about setting up WordPressCS to work in your IDE.


## Running your code through WordPressCS automatically using Continuous Integration tools

- [Running in GitHub Actions](https://github.com/WordPress/WordPress-Coding-Standards/wiki/Running-in-GitHub-Actions)
- [Running in Travis](https://github.com/WordPress/WordPress-Coding-Standards/wiki/Running-in-Travis)


## Fixing errors or ignoring them

You can find information on how to deal with some of the more frequent issues in the [wiki](https://github.com/WordPress/WordPress-Coding-Standards/wiki).

### Tools shipped with WordPressCS

Since version 1.2.0, WordPressCS has a special sniff category `Utils`.

This sniff category contains some tools which, generally speaking, will only be needed to be run once over a codebase and for which the fixers can be considered _risky_, i.e. very careful review by a developer is needed before accepting the fixes made by these sniffs.

The sniffs in this category are disabled by default and can only be activated by adding some properties for each sniff via a custom ruleset.

At this moment, WordPressCS offer the following tools:
* `WordPress.Utils.I18nTextDomainFixer` - This sniff can replace the text domain used in a code-base.
    The sniff will fix the text domains in both I18n function calls as well as in a plugin/theme header.
    Passing the following properties will activate the sniff:
    - `old_text_domain`: an array with one or more (old) text domain names which need to be replaced;
    - `new_text_domain`: the correct (new) text domain as a string.


## Contributing

See [CONTRIBUTING](.github/CONTRIBUTING.md), including information about [unit testing](.github/CONTRIBUTING.md#unit-testing) the standard.

Anyone contributing to the WordPress Coding Standards is expected to conduct themselves in accordance with the WordPress project's [Code of Conduct](https://github.com/WordPress/.github/blob/trunk/CODE_OF_CONDUCT.md).

## Funding

If you want to sponsor the work on WordPressCS, you can do so by donating to the [PHP_CodeSniffer Open Collective](https://opencollective.com/php_codesniffer).

## License

See [LICENSE](LICENSE) (MIT).
