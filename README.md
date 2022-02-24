**OPTIMETA OJS Plugin Shared Library**

Shared Library for the OPTIMETTA OJS Plugins

This library can be used as a submodule in other repositories. 

https://github.com/TIBHannover/optimeta-plugin-shared


**Install / update**

Add the following to your project composer.json

```
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/tibhannover/optimeta-plugin-shared.git"
    }
  ],
```

```
  "require": {
    "tibhannover/optimeta": "^v1.0.0"
  }
```
Execute the following command to install
```
composer install
```
or the following command to update
```
composer update
```

Autoloading is defined in the composer.json of this library. 
Execute the following to update the composer autoload:
```
composer dump-autoload
```
