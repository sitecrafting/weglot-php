<!-- logo -->
<img src="https://cdn.weglot.com/logo/logo-hor.png" height="40" />

<!-- tags -->
[![Latest Stable Version](https://poser.pugx.org/weglot/weglot-php/v/stable)](https://packagist.org/packages/weglot/weglot-php)
[![BuildStatus](https://travis-ci.org/weglot/weglot-php.svg?branch=master)](https://travis-ci.org/weglot/weglot-php)
[![Code Climate](https://codeclimate.com/github/weglot/weglot-php/badges/gpa.svg)](https://codeclimate.com/github/weglot/weglot-php)
[![License](https://poser.pugx.org/weglot/weglot-php/license)](https://packagist.org/packages/weglot/weglot-php)

## Overview
This library allows you to quickly and easily use the Weglot API via PHP.

This library represents the beginning of a new path for Weglot. We want this library to be community driven and Weglot led. We need your help to realize this goal. To help make sure we are building the right things in the right order, we ask that you create issues and pull requests or simply upvote or comment on existing issues or pull requests.

## Requirements
- PHP version 5.5 and later
- Weglot service, starting at [free level](https://dashboard.weglot.com/register)

## Installation
You can install the bindings via [Composer](https://getcomposer.org/). Run the following command:

```bash
composer require weglot/weglot-php
```

To use the library, use Composer's [autoload](https://getcomposer.org/doc/01-basic-usage.md#autoloading):

```php
require_once __DIR__. '/vendor/autoload.php';
```

## Getting Started

Simple usage looks like:

```php
$translate = new \Weglot\Client\Api\TranslateEntry([
    'language_from' => 'en',
    'language_to' => 'fi',
    'title' => 'Lorem ipsum dolor sit amet',
    'request_url' => 'https://foo.bar/baz',
    'bot' => BotType::HUMAN
])->getInputWords()
    ->addOne(new \Weglot\Client\Api\WordEntry('This is a blue car', WordType::TEXT))
    ->addOne(new \Weglot\Client\Api\WordEntry('This is a black car', WordType::TEXT));

$client = new \Weglot\Client\Client('YOUR_WEGLOT_API_KEY');
$translate = new \Weglot\Client\Endpoint\Translate($translate, $client);
$object = $translate->handle();

var_dump($object);
```

## Examples

For more usage examples, such as: other endpoints, caching, parsing.

You can take a look at: [examples](./examples) folder. You'll find a short README with details about each example.

## Documentation

Soon (tm)

## About
`weglot-php` is guided and supported by the Weglot Developer Team.

`weglot-php` is maintained and funded by Weglot SAS. 
The names and logos for `weglot-php` are trademarks of Weglot SAS.

## License
[The MIT License (MIT)](LICENSE.txt)
