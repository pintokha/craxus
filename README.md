## Craxus

![](https://img.shields.io/packagist/l/pintokha/craxus?style=flat-square)
![](https://img.shields.io/packagist/dm/pintokha/craxus?style=flat-square)

Register at <https://craxus.io> and use the application credentials within your app as shown below.

## Supported platforms
* PHP - supports PHP versions: 7.4
* Laravel(& dusk) - version 6.x

## Installation
You can install Craxus watcher via composer package
```
composer require pintokha/craxus --dev
```
Or add to ```composer.json```:
```
"require-dev": {
        "pintokha/craxus": "1.0.0"
}
```
and then run ```composer update```.

## Usage
#### Laravel, Symfony
After installing the package, you need to update the ```phpunit.xml``` file by adding the 
extension from the package (in tag \<extensions\>), example:
```
<extension class="Pintokha\Craxus\Watcher">
    <arguments>
        <boolean>true</boolean> 
        <string>your API token</string>
        <string>project ID</string>
    </arguments>
</extension>
```
First arg: extension status - on(true), off(false)

If you are using Laravel dusk you need to create or update a ```phpunit.dusk.xml``` by adding the extension above.

All data you can get on your Craxus account. <https://craxus.io>