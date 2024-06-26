<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser;

use Exception;
use Filefabrik\Bootraiser\Support\FindBootable;
use Filefabrik\Bootraiser\Support\PackageConfig;
use Filefabrik\Bootraiser\Support\Str\Pathering;
use Generator;
use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\View\Factory as ViewFactory;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Finder\Finder;

/**
 * insert it into your YourPackageServiceProvider
 */
trait Bootraiser
{
    protected ?PackageConfig $bootraiserPackage = null;

    /**
     * @return ServiceProvider $this
     */
    protected function parentServiceProvider(): ServiceProvider
    {
        return $this;
    }

    /**
     * @param $config
     *
     * @return PackageConfig
     * @throws Exception
     */
    protected function bootraiserPackage($config = null): PackageConfig
    {
        if ($this->bootraiserPackage && !$config) {
            return $this->bootraiserPackage;
        }
        // for each package only one instance, if need create another method to handle the full namespaced ServiceProviders
        // to alternative methods to check, that called is a Service Provider
        $packageName = Str::beforeLast(static::class, '\\Support\\');

        return $this->bootraiserPackage = BootraiserManager::getPackageConfig($packageName, $config ?? $this);
    }

    /**
     * Methods called from the YourPackageServiceProvider::register()
     *
     * @param array              $parts
     *
     * @param PackageConfig|null $packageConfig
     *
     * @return void
     */
    protected function registerBootraiserServices(array $parts, ?PackageConfig $packageConfig = null): void
    {
        foreach ($parts as $part) {
            $this->registerBootraiserService($part, $packageConfig);
        }
    }

    /**
     * Methods called from the YourPackageServiceProvider::register()
     *
     * @param array              $parts
     *
     * @param PackageConfig|null $packageConfig
     *
     * @return void
     */
    protected function integrateBootraiserServices(array $parts, ?PackageConfig $packageConfig = null): void
    {
        foreach ($parts as $part) {
            $this->integrateBootraiserService($part, $packageConfig);
        }
    }

    /**
     * Booting Helper while created this package with filefabrik/paxsy.
     * You can customize the booting methods or write your own booting.
     * The paxsy booting methods do not have any dependency magic
     *
     * @param array              $parts
     *
     * @param PackageConfig|null $packageConfig
     *
     * @return void
     * @throws Exception
     */
    protected function bootBootraiserServices(array $parts, ?PackageConfig $packageConfig = null): void
    {
        foreach ($parts as $bootingPart) {
            $this->bootBootraiserService($bootingPart, $packageConfig);
        }
    }

    /**
     * @param string             $parts
     *
     * @param PackageConfig|null $packageConfig
     *
     * @return bool
     */
    protected function registerBootraiserService(string $parts, ?PackageConfig $packageConfig = null): bool
    {
        return $this->callPackageMethod('registering', $parts, $packageConfig);
    }

    /**
     * @param string             $part
     *
     * @param PackageConfig|null $packageConfig
     *
     * @return bool
     */
    protected function integrateBootraiserService(string $part, ?PackageConfig $packageConfig = null): bool
    {
        return $this->callPackageMethod('integrate', $part, $packageConfig);
    }

    /**
     * @param string             $part
     *
     * @param PackageConfig|null $packageConfig
     *
     * @return bool
     * @throws Exception
     */
    protected function bootBootraiserService(string $part, ?PackageConfig $packageConfig = null): bool
    {
        if ($this->callPackageMethod('booting', $part, $packageConfig)) {
            return true;
        }
        // by class
        $instance = FindBootable::findBootable($part);
        if ($instance) {
            $instance->booting($packageConfig ?? $this->bootraiserPackage());

            return true;
        }

        return false;
    }

    /**
     * @param string             $prefix
     * @param string             $methodPart
     *
     * @param PackageConfig|null $packageConfig
     *
     * @return bool
     */
    protected function callPackageMethod(string $prefix, string $methodPart, ?PackageConfig $packageConfig = null): bool
    {
        $serviceProvider = $this->parentServiceProvider();
        $method          = $prefix . Str::ucfirst($methodPart);

        if (method_exists($serviceProvider, $method)) {
            // package_base_path,
            //package-name: lower kebab case (last part of the composer name="vendor/package-name"),
            //namespace in composer psr-4 /src
            $this->$method($packageConfig);

            return true;
        }

        return false;
    }

    /**
     * Boot the Web-Route
     *
     * @see https://laravel.com/docs/11.x/packages#routes
     *
     * @param PackageConfig|null $packageConfig
     *
     * @return void
     * @throws Exception
     */
    protected function bootingRoutes(?PackageConfig $packageConfig = null): void
    {
        $packageConfig ??= $this->bootraiserPackage();
        $routeFiles    = $packageConfig->concatPackagePath('routes/web.php');
        if (file_exists($routeFiles)) {
            $parentServiceProvider = $this->parentServiceProvider();
            // todo custom output format for packages as developers preferences
            $overrideRoutePath     = base_path('/routes/web-' . $packageConfig->groupOrVendorName() . '.php');
            // use override route
            // todo make override and original route load if need. Otherwise override is override
            if (is_file($overrideRoutePath)) {
                $parentServiceProvider->loadRoutesFrom($overrideRoutePath);
            }
            else {
                $parentServiceProvider->loadRoutesFrom($routeFiles);
            }

            $parentServiceProvider
                ->publishes(
                    [$routeFiles => base_path('/routes/web-' . $packageConfig->groupOrVendorName() . '.php')],
                    $packageConfig->concatGroupName('routes'),
                )
            ;
        }
    }

    /**
     * Publish Migrations if need
     * // todo make prefix for published migration files
     * @see https://laravel.com/docs/11.x/packages#migrations
     *
     * @param PackageConfig|null $packageConfig
     *
     * @return void
     * @throws Exception
     */
    protected function bootingMigrations(?PackageConfig $packageConfig = null): void
    {
        $packageConfig ??= $this->bootraiserPackage();
        $migrationDir  = $packageConfig->concatPackagePath('database/migrations');

        if (is_dir($migrationDir)) {
            $pp = $this->parentServiceProvider();
    // laravel 11
            if (method_exists($pp, 'publishesMigrations')) {
                $pp->publishesMigrations(
                // todo perhaps each file with a package prefix
                    [$migrationDir => database_path('migrations')],
                    $packageConfig->concatGroupName('migrations'),
                );
            }
            else {
                // laravel 10 compatibility
                $generator = function(string $directory) use ($packageConfig): Generator {
                    foreach ($this->app->make('files')
                                       ->allFiles($directory) as $file) {
                        yield $file->getPathname() => $this->app->databasePath(
                            'migrations/' . $packageConfig->groupOrVendorName().'_' . Str::after($file->getFilename(),
                                                                                    '00_00_00_000000'),
                        );
                    }
                };

                $pp->publishes(iterator_to_array($generator($migrationDir)),
                               $packageConfig->concatGroupName('migrations'));
            }
        }
    }

    /**
     * If integrated on `php artisan migrate:status` these migrations will be offered and executed
     *
     * @param PackageConfig|null $packageConfig
     *
     * @return void
     * @throws Exception
     */
    protected function integrateMigrations(?PackageConfig $packageConfig = null): void
    {
        $migrationDir = ($packageConfig ?? $this->bootraiserPackage())->concatPackagePath('database/migrations');
        if (is_dir($migrationDir)) {
            // not need to publish migrations.
            // migrations are available directly from package
            $this->loadMigrationsFrom($migrationDir);
        }
    }

    /**
     * Seeder is need to fill tables with default values set.
     * Set only a flag, the package should be handelt by bootraiser db:seed
     *
     * @param PackageConfig|null $packageConfig
     *
     * @return void
     * @throws Exception
     */
    protected function bootingSeeder(?PackageConfig $packageConfig = null): void
    {
        ($packageConfig ?? $this->bootraiserPackage())->add('trackSeeders', true);
    }

    /**
     * Loading translations from package/lang
     *
     * @see https://laravel.com/docs/11.x/packages#language-files
     *
     * @param PackageConfig|null $packageConfig
     *
     * @return void
     * @throws Exception
     */
    protected function bootingTranslations(?PackageConfig $packageConfig = null): void
    {
        $packageConfig ??= $this->bootraiserPackage();
        $langDir       = $packageConfig->concatPackagePath('lang');
        if (is_dir($langDir)) {
            $serviceProvider = $this->parentServiceProvider();
            $serviceProvider->loadTranslationsFrom($langDir, $packageConfig->getVendorPackageName());
            $serviceProvider->publishes(
                [$langDir => $serviceProvider->app->langPath('vendor/' . $packageConfig->getVendorPackageName())],
                $packageConfig->concatGroupName('translations'),
            );
        }
    }

    /**
     * Enable blade views in laravel or publish
     *
     * @param PackageConfig|null $packageConfig
     *
     * @return void
     * @throws Exception
     */
    protected function bootingViews(?PackageConfig $packageConfig = null): void
    {
        $packageConfig ??= $this->bootraiserPackage();
        $viewsDir      = $packageConfig->concatPackagePath('resources/views');
        if (is_dir($viewsDir)) {
            $serviceProvider = $this->parentServiceProvider();
            $serviceProvider->loadViewsFrom($viewsDir, $packageConfig->getNamespace());
            $serviceProvider->publishes(
                [$viewsDir => resource_path(Pathering::concat('views/vendor', $packageConfig->getVendorPackageName()) )],
                $packageConfig->concatGroupName('views'),
            );

            $serviceProvider->callAfterResolving(
                'view',
                function(ViewFactory $view_factory) use ($packageConfig) {
                    $view_factory->addNamespace(
                        $packageConfig->getVendorPackageName(),
                        $packageConfig->concatPackagePath('resources/views'),
                    );
                },
            );
        }
    }

    /**
     * Boot commands if there is any
     *
     * @see https://laravel.com/docs/11.x/packages#commands
     *
     * @throws ReflectionException
     */
    protected function bootingCommands(?PackageConfig $packageConfig = null): void
    {
        $packageConfig ??= $this->bootraiserPackage();
        // does not boot /app commands they are booted by laravel core
        $commandDir    = $packageConfig->concatPackagePath('src/Console/Commands');
        if (app()->runningInConsole() && is_dir($commandDir)) {
            $finder = Finder::create()
                            ->files()
                            ->in($commandDir)
                            ->name('*.php')
            ;

            foreach ($finder->getIterator() as $file) {
                $cls     = $file->getBasename('.php');
                $command = $packageConfig->concatPackageNamespace('Console\Commands' , $cls);

                if (is_subclass_of($command, Command::class) && !(new ReflectionClass($command))->isAbstract()) {
                    Artisan::starting(fn(Artisan $artisan) => $artisan->resolve($command));
                }
            }
        }
    }

    /**
     * publish the config
     *
     * @see https://laravel.com/docs/11.x/packages#publishing-file-groups
     *
     * @param PackageConfig|null $packageConfig
     *
     * @return void
     * @throws Exception
     */
    protected function bootingConfig(?PackageConfig $packageConfig = null): void
    {
        $packageConfig ??= $this->bootraiserPackage();
        $configFile    = $this->helperConfigFiles($packageConfig);
        if ($configFile) {
            $this->parentServiceProvider()
                 ->publishes(
                     [$configFile => config_path($packageConfig->groupOrVendorName() . '.php')],
                     $packageConfig->concatGroupName('config'),
                 )
            ;
        }
    }

    /**
     * Called via registering
     *
     * @param PackageConfig|null $packageConfig
     *
     * @return void
     * @throws Exception
     */
    protected function registeringConfig(?PackageConfig $packageConfig = null): void
    {
        $packageConfig ??= $this->bootraiserPackage();
        $configFile    = $this->helperConfigFiles($packageConfig);
        if ($configFile) {
            $this->parentServiceProvider()
                 ->mergeConfigFrom($configFile, $packageConfig->groupOrVendorName())
            ;
        }
    }

    /**
     * Locate the config DRY
     *
     * @param PackageConfig|null $packageConfig
     *
     * @return string|null
     * @throws Exception
     * @internal
     */
    protected function helperConfigFiles(?PackageConfig $packageConfig = null): ?string
    {
        $configFile = ($packageConfig ?? $this->bootraiserPackage())->concatPackagePath('config/config.php');

        return is_file($configFile) ? $configFile : null;
    }
}
