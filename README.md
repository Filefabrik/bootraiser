# Bootraiser

Booting Utility for Laravel custom Packages

### Features

* Bootraiser saves you all the booting|publishable boilerplate required for a Laravel package.
* Bootraiser can be easily integrated into existing packages.
* Bootraiser is completely based on laravel
* no “extra-magic” packages necessary

If you write your own Laravel packages, parts of your package usually have to be booted in Laravel.

This can sometimes cost an unnecessary amount of time.

Filefabrik-Bootraiser provides you with all important Laravel boot methods immediately and without much configuration
effort.
It is also quite cool if the Laravel “publish” methods are supported so that views|translations|configs and so on, overrides can be
published later.

* documentation: https://bootraiser.filefabrik.com 
* github-project: https://github.com/Filefabrik/bootraiser
* packagist.org: https://packagist.org/packages/filefabrik/bootraiser

**Bootraiser strictly uses [SemVer](https://semver.org/) so please use `~2.0`**

Installation:

```shell
composer require "filefabrik/bootraiser:~2.0"
```

## Modify your YourPackageServiceProvider.php

Then go to your provider file, which is usually under:

`~/packages/your-package/src/Providers/YourPackageServiceProvider.php`

YourPackageServiceProvider.php file looks like this:

```php
<?php

namespace YourCompanyVendor\YourPackage\Providers;

use Illuminate\Support\ServiceProvider;

class YourPackageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        parent::register();
        Raiser::forProvider($this)
              ->loadConfigs()
        ;
    }

    public function boot(): void
    {
        Raiser::forProvider($this)
              ->publishConfigs()
              ->migrations()
              ->routes()
              ->translations()
              ->views()
              ->components()
              ->commands()
              ->livewire()
        ;
    }

}
```

Then which components you want to boot with Bootraiser as an Array or via Methods as above shown.

Note: You can enter all parts as boot parts. Bootraiser only boots the parts that are actually in your package.

The following "load" mechanisms are available to you:

* configs
* routes
* migrations
* translations
* views + view components
* commands
* livewire
* seeder, factory
* events, listener
