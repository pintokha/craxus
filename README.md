## Craxus

![](https://img.shields.io/packagist/l/pintokha/craxus?style=flat-square)
![](https://img.shields.io/packagist/dm/pintokha/craxus?style=flat-square)

Register at <https://craxus.io> and use the application credentials within your app as shown below.

## Supported platforms
* PHP - supports PHP versions:  >= 7.1
* Laravel(& dusk) - version >= 5.6

## Installation
You can install Craxus watcher via composer package
```
composer require pintokha/craxus --dev
```

## Usage
#### Laravel, Symfony
After installing the package, you need to add in your env file for testing next variables:
```
CRAXUS_ENABLE=true
CRAXUS_SECRET='your_secret_key'
CRAXUS_APP_ID='your_app_id'
```

All data you can get on your Craxus account. <https://craxus.io>