# Booterais

Booting Utility for Laravel custom Packages   

If you write your own Laravel packages, parts of your package usually have to be booted in Laravel.

This can sometimes cost an unnecessary amount of time.

Filefabrik-Bootraiser provides you with all important Laravel boot methods immediately and without much configuration effort.
It is also quite cool if the Laravel “publish” methods are supported so that views|translations|config overrides can be published later.

**current state is "dev". there are no tests. but they are under construction**

Installation:

```shell
composer install filefabrik/bootraiser
```

Then go to your provider file, which is usually under:

`~/packages/your-package/src/Providers/YourPackageServiceProvider.php`

YourPackageServiceProvider.php file looks like this:

```php
<?php

namespace YourCompanyVendor\YourPackage\Providers;

use Filefabrik\Bootraiser\Bootraiser;
use Filefabrik\Bootraiser\Support\PackageConfig;use Illuminate\Support\ServiceProvider;

class YourPackageServiceProvider extends ServiceProvider
{
    // insert this Magic Trait :)
    use Bootraiser;

    public function register()
    {
    }

    public function boot(): void
    {
       $packageConfig = new PackageConfig(basePath         : realpath(__DIR__ . '/../../') . '/',
                                          vendorPackageName: 'your-package',
                                          namespace        : 'YourCompanyVendor\\YourPackage\\');
       
       // parts to boot if they are already exists in your code 
       $bootParts = [
       	    'Routes',
			'Migrations',
			'Translations',
			'Views',
			'Commands', 
			'Config',
            // 3rd Party package
			'Livewire',
		];
 
        /* Easy boot utility. You can replace all the booted service/parts with your own*/
       $this->bootraiserService($packageConfig,$bootParts);
    }

}
```

To use Bootraiser, `use Bootraiser;` must be included.

Next comes the configuration of your package.
Note:
The path must be above your ./src folder so that the lang|config|migration/database folder of Bootraiser can be found correctly.

Then which components you want to boot with Bootraiser as an Array.
Note:
You can enter all parts as boot parts. Bootraiser only boots the parts that are actually in your package.

## Split Bootraiser Boot process

Subdivide the boot process with boot raiser if required.

```php
<?php 
...
public function boot(): void
    {
       $packageConfig = new PackageConfig(basePath         : realpath(__DIR__ . '/../../') . '/',
                                          vendorPackageName: 'your-package',
                                          namespace        : 'YourCompanyVendor\\YourPackage\\');
       
       // parts to boot if they are already exists in your code 
       $bootParts = [
       	    'Routes',
			'Migrations',
			'Translations',
		];
 
        /* Easy boot utility. You can replace all the booted service/parts with your own*/
       $this->bootraiserService($packageConfig,$bootParts);
       
       /**
        * your custom boot stuff
        */
       
       // boot the rest if need 
       $bootParts2 = [
            'Views',
            'Commands',
            'Config',
            // 3rd Party package
            'Livewire',
            ];
       $this->bootraiserService($packageConfig,$bootParts2);
    }
...
?>
```

The following boot mechanisms are available to you:

### Routes

`packages/your-package/routes/web.php`

@see https://laravel.com/docs/11.x/packages#routes

### Migrations

`packages/your-package/database/migrations/*`

```shell
php artisan vendor:publish --tag=your-package-migrations
```

@see https://laravel.com/docs/11.x/packages#migrations

### Translations (Language-Files)

`packages/your-package/lang/*`

```shell
php artisan vendor:publish --tag=your-package-translations
```

https://laravel.com/docs/11.x/packages#language-files

### Views (blade) and View Components

* loadViews
* publish your blade files for overrides if need

```shell
php artisan vendor:publish --tag=your-package-views
```
* register your package to view components
 
@see https://laravel.com/docs/11.x/packages#views

### Commands

Boot your commands if any are existing.

`packages/your-package/src/Console/Commands`

@see https://laravel.com/docs/11.x/packages#commands


### Config

`packages/your-package/config/config.php`

```shell
php artisan vendor:publish --tag=your-package-config
```
`config` is singular! 

will output to `config/your-package.php` or with custom `$packageConfig->setGroupName('cooler')` to `config/cooler.php`

### Livewire

If you create your own Livewire views, Livewire is also supported and booted.
* blade directory: `packages/your-package/resource/views/livewire/*`
* Livewire Component Directory `packages/your-package/src/Livewire/`

A note on vendor:publish --tag=“your-package”-views|translations|migrations
If your package name is too long or cumbersome to create a memorable group name, simply set a different identifier for the group names

```php
<?php 
...
$packageConfig = new PackageConfig(basePath         : realpath(__DIR__ . '/../../') . '/',
                                   vendorPackageName: 'your-package',
                                   namespace        : 'YourCompanyVendor\\YourPackage\\');
$packageConfig->setGroupName('cooler');
?>
```
Or inline:
```php
<?php 
...
$packageConfig = (new PackageConfig(basePath         : realpath(__DIR__ . '/../../') . '/',
                                    vendorPackageName: 'your-package',
                                    namespace        : 'YourCompanyVendor\\YourPackage\\'))
                                   ->setGroupName('cooler');
?>
```
Now all your publish tag options will look like `--tag=cooler-views`

```shell
php artisan vendor:publish --tag=cooler-views
```