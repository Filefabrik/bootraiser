<?php declare(strict_types=1);
/**
 * PHP version 8.2
 *
 */
/** @copyright-header * */

namespace Filefabrik\Booteraise;

use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\View\Factory as ViewFactory;
use Livewire\Livewire;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Finder\Finder;

/**
 * insert it into your YourPackageServiceProvider
 */
trait Bootraiser
{
    /**
     * Booting Helper while created this package with filefabrik/paxsy.
     * You can customize the booting methods or write your own booting.
     * The paxsy booting methods do not have any dependency magic
     *
     * @param string $packageRoot
     * @param string $packageName
     * @param string $vendorPackageNamespace
     *
     * @return void
     */
    protected function bootraiserService(string $packageRoot, string $packageName, string $vendorPackageNamespace): void
    {
        /**
         *
         */
        $bootingParts = [
            'Views',
            'Livewire',
            'Translations',
            'Routes',
            'Migrations',
            'Commands',
        ];



        foreach ($bootingParts as $bootingPart) {
            // package_base_path, package-name: lower kebab case (last part of the composer name="vendor/package-name"), namespace in composer psr-4 /src
            $this->{'booting' . $bootingPart}($packageRoot, $packageName, $vendorPackageNamespace);
        }
    }

    /**
     *
     * @param string $packageRoot
     *
     * @return void
     */
    protected function bootingRoutes(string $packageRoot): void
    {
        if (file_exists($routeFiles = $packageRoot . 'routes/web.php')) {
            $this->loadRoutesFrom($routeFiles);
        }
    }

    /**
     * Boot commands if there is any
     *
     * @throws ReflectionException
     */
    protected function bootingCommands(string $packageRoot, string $packageName, string $vendorPackageNamespace): void
    {
        if (is_dir($commandDir = $packageRoot . 'src/Console/Commands')) {
            $finder = Finder::create()
                            ->files()
                            ->in($commandDir)
                            ->name('*.php')
            ;

            foreach ($finder->getIterator() as $file) {
                $cls     = $file->getBasename('.php');
                $command = $vendorPackageNamespace . 'Console\\Commands\\' . $cls;

                if (is_subclass_of($command, Command::class) && !(new ReflectionClass($command))->isAbstract()) {
                    Artisan::starting(fn(Artisan $artisan) => $artisan->resolve($command));
                }
            }
        }
    }

    /**
     * Enable blade views in laravel or publish
     *
     * @param string $packageRoot
     * @param string $packageName
     * @param string $vendorPackageNamespace
     *
     * @return void
     */
    protected function bootingViews(string $packageRoot, string $packageName, string $vendorPackageNamespace): void
    {
        if (is_dir($viewsDir = $packageRoot . 'resources/views')) {
            $this->loadViewsFrom($viewsDir, $vendorPackageNamespace);
            $this->publishes([$viewsDir => resource_path('views/vendor/' . $packageName),],
                             $packageName . '-views');

            $this->callAfterResolving(
                'view',
                function(ViewFactory $view_factory) use ($packageName, $packageRoot) {
                    $view_factory->addNamespace($packageName, $packageRoot . 'resources/views');
                },
            );
        }
    }

    /**
     * Publish Migrations if need
     *
     * @param string $packageRoot
     * @param string $packageName
     *
     * @return void
     */
    protected function bootingMigrations(string $packageRoot, string $packageName,): void
    {
        if (is_dir($migrationDir = $packageRoot . 'database/migrations')) {
            $this->publishesMigrations([$migrationDir => database_path('migrations'),],
                                       $packageName . '-migrations');
        }
    }

    /**
     * Loading translations from package/lang
     *
     * @param string $packageRoot
     * @param string $packageName
     * @param string $vendorPackageNamespace
     *
     * @return void
     */
    protected function bootingTranslations(string $packageRoot,
                                           string $packageName,
                                           string $vendorPackageNamespace): void
    {
        if (is_dir($langDir = $packageRoot . 'lang')) {
            $this->loadTranslationsFrom($langDir, $vendorPackageNamespace);
            $this->publishes([$langDir => $this->app->langPath('vendor/' . $packageName),],
                             $packageName . '-translations');
        }
    }

    /**
     * Booting Livewire if exists
     *
     * @param string $packageRoot
     * @param string $packageName
     * @param string $vendorPackageNamespace
     *
     * @return void
     */
    protected function bootingLivewire(string $packageRoot, string $packageName, string $vendorPackageNamespace): void
    {
        if (is_dir($livewireDir = $packageRoot . 'src/Livewire/')) {
            $finder = Finder::create()
                            ->files()
                            ->in($livewireDir)
                            ->name('*.php')
            ;

            foreach ($finder->getIterator() as $file) {
                $cls           = $file->getBasename('.php');
                $className     = $vendorPackageNamespace . 'Livewire\\' . $cls;
                $componentName = Str::of($cls)
                                    ->explode('/')
                                    ->filter()
                                    ->map([Str::class, 'kebab'])
                                    ->implode('.')
                ;

                if (class_exists(Livewire::class) && class_exists($className)) {
                    Livewire::component($packageName . '::' . $componentName, $className);
                }
            }
        }
    }
}
