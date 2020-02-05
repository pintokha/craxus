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
Or add to ```composer.json```:
```
"require-dev": {
        "pintokha/craxus": "1.0.1"
}
```
and then run ```composer update```.

## Usage
#### Laravel, Symfony
After installing the package, you need to add in your env file for testing next variables:
```
CRAXUS_ENABLE=true
CRAXUS_API_TOKEN=your_api_token
CRAXUS_PROJECT_ID=project_id
```
First arg: extension status - on(true), off(false).

All data you can get on your Craxus account. <https://craxus.io>