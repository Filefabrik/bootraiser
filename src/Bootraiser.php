<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser;

use Filefabrik\Bootraiser\Support\FindBootable;
use Filefabrik\Bootraiser\Support\PackageConfig;
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
	/**
	 * @return ServiceProvider $this
	 */
	protected function parentServiceProvider(): ServiceProvider
	{
		return $this;
	}

	/**
	 * Booting Helper while created this package with filefabrik/paxsy.
	 * You can customize the booting methods or write your own booting.
	 * The paxsy booting methods do not have any dependency magic
	 *
	 * @param PackageConfig $packageConfig
	 * @param array         $bootingParts
	 *
	 * @return void
	 */
	protected function bootraiserService(PackageConfig $packageConfig, array $bootingParts): void
	{
		foreach ($bootingParts as $bootingPart) {
			$this->bootingPart($packageConfig, $bootingPart);
		}
	}

	/**
	 * @param PackageConfig $packageConfig
	 * @param string        $bootingPart
	 *
	 * @return bool
	 */
	protected function bootingPart(PackageConfig $packageConfig, string $bootingPart): bool
	{
		$serviceProvider = $this->parentServiceProvider();
		$method          = 'booting'.Str::ucfirst($bootingPart);

		if (method_exists($serviceProvider, $method)) {
			// package_base_path, package-name: lower kebab case (last part of the composer name="vendor/package-name"), namespace in composer psr-4 /src
			$this->$method($packageConfig);

			return true;
		}
		// by class
		$instance = FindBootable::findBootable($bootingPart);
		if ($instance) {
			$instance->booting($packageConfig);

			return true;
		}

		return false;
	}

	/**
	 * Boot the Web-Route
	 *
	 * @see https://laravel.com/docs/11.x/packages#routes
	 *
	 * @param PackageConfig $packageConfig
	 *
	 * @return void
	 */
	protected function bootingRoutes(PackageConfig $packageConfig): void
	{
		$routeFiles = $packageConfig->concatPath('routes/web.php');
		if (file_exists($routeFiles)) {
			$this->parentServiceProvider()
				 ->loadRoutesFrom($routeFiles)
			;
		}
	}

	/**
	 * Publish Migrations if need
	 *
	 * @see https://laravel.com/docs/11.x/packages#migrations
	 *
	 * @param PackageConfig $packageConfig
	 *
	 * @return void
	 */
	protected function bootingMigrations(PackageConfig $packageConfig): void
	{
		$migrationDir = $packageConfig->concatPath('database/migrations');
		if (is_dir($migrationDir)) {
			$this->parentServiceProvider()
				 ->publishesMigrations(
				 	[$migrationDir => database_path('migrations')],
				 	$packageConfig->concatGroupName('migrations'),
				 )
			;
		}
	}

	/**
	 * Loading translations from package/lang
	 *
	 * @see https://laravel.com/docs/11.x/packages#language-files
	 *
	 * @param PackageConfig $packageConfig
	 *
	 * @return void
	 */
	protected function bootingTranslations(PackageConfig $packageConfig): void
	{
		$langDir = $packageConfig->concatPath('lang');
		if (is_dir($langDir)) {
			$serviceProvider = $this->parentServiceProvider();
			$serviceProvider->loadTranslationsFrom($langDir, $packageConfig->getVendorPackageName());
			$serviceProvider->publishes(
				[$langDir => $serviceProvider->app->langPath('vendor/'.$packageConfig->getVendorPackageName())],
				$packageConfig->concatGroupName('translations'),
			);
		}
	}

	/**
	 * Enable blade views in laravel or publish
	 *
	 * @param PackageConfig $packageConfig
	 *
	 * @return void
	 */
	protected function bootingViews(PackageConfig $packageConfig): void
	{
		$viewsDir = $packageConfig->concatPath('resources/views');
		if (is_dir($viewsDir)) {
			$serviceProvider = $this->parentServiceProvider();
			$serviceProvider->loadViewsFrom($viewsDir, $packageConfig->getNamespace());
			$serviceProvider->publishes(
				[$viewsDir => resource_path('views/vendor/'.$packageConfig->getVendorPackageName())],
				$packageConfig->concatGroupName('views'),
			);

			$serviceProvider->callAfterResolving(
				'view',
				function(ViewFactory $view_factory) use ($packageConfig) {
					$view_factory->addNamespace(
						$packageConfig->getVendorPackageName(),
						$packageConfig->concatPath('resources/views'),
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
	protected function bootingCommands(PackageConfig $packageConfig): void
	{
		$commandDir = $packageConfig->concatPath('src/Console/Commands');
		if (app()->runningInConsole() && is_dir($commandDir)) {
			$finder = Finder::create()
							->files()
							->in($commandDir)
							->name('*.php')
			;

			foreach ($finder->getIterator() as $file) {
				$cls     = $file->getBasename('.php');
				$command = $packageConfig->concatNamespace('Console\\Commands\\'.$cls);

				if (is_subclass_of($command, Command::class) && ! (new ReflectionClass($command))->isAbstract()) {
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
	 * @param PackageConfig $packageConfig
	 *
	 * @return void
	 */
	protected function bootingConfig(PackageConfig $packageConfig): void
	{
		$configFile = $packageConfig->concatPath('config/config.php');
		if (is_file($configFile)) {
			$this->parentServiceProvider()
				 ->publishes(
				 	[$configFile => config_path($packageConfig->groupOrVendorName().'.php')],
				 	$packageConfig->concatGroupName('config'),
				 )
			;
		}
	}
}
