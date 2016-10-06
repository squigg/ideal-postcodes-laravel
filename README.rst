ideal-postcodes-laravel
=============

[![Build Status](https://travis-ci.org/squigg/ideal-postcodes-laravel.png?branch=master)](https://travis-ci.org/squigg/ideal-postcodes-laravel)
[![Coverage Status](https://coveralls.io/repos/squigg/ideal-postcodes-laravel/badge.png?branch=master)](https://coveralls.io/r/squigg/ideal-postcodes-laravel)
[![Dependency Status](https://www.versioneye.com/package/php--squigg--ideal-postcodes-laravel/badge.png)](https://www.versioneye.com/package/php--squigg--ideal-postcodes-laravel)

[![Latest Stable Version](https://poser.pugx.org/squigg/ideal-postcodes-laravel/v/stable.png)](https://packagist.org/packages/squigg/ideal-postcodes-laravel)
[![Total Downloads](https://poser.pugx.org/squigg/ideal-postcodes-laravel/downloads.png)](https://packagist.org/packages/squigg/ideal-postcodes-laravel)

PHP Laravel 5 package for the [Ideal Postcodes API](https://ideal-postcodes.co.uk).

# Prerequisites

- PHP 5.5+
- Laravel 5.x
- Ideal Postcodes Account and API Key

# Installation

## Install by composer
You can find this library on [Packagist](https://packagist.org/packages/squigg/ideal-postcodes-laravel).

To install ideal-postcodes-laravel with Composer, run the following command:

```sh
$ composer require squigg/ideal-postcodes-laravel
```

# Configuration

The package publishes a configuration file to your config directory where you can update the configuration for your app.

```sh
$ php artisan vendor:publish --tag=config
```

Add the IdealPostcodesServiceProvider and (if desired) the Facade alias  to your `app.php`:
```php
'providers' => [

    ...

    'Squigg\IdealPostcodes\IdealPostcodesServiceProvider',

],

'aliases' => [

    ...

    'IdealPostcodes' => 'Squigg\IdealPostcodes\Facades\IdealPostcodesFacade',

],
```

Add an `IDEALPOSTCODES_API_KEY` to your `.env` file with your Ideal Postcodes API key
```
IDEALPOSTCODES_API_KEY=ak_abcdefghijklmnopqrstuvwxyz
```

## Configuration File Settings

All available settings are documented within the config/ideal-postcodes.php configuration file.

# Usage

Simply call the `\IdealPostcodes` Facade, or add a dependency in any constructor to IdealPostcodes

# Change log

[See changelog](CHANGELOG.md)



Copyright © 2016 Steve Strugnell. Released under the `MIT License <docs/license.rst>`_.
