<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Support;

use Closure;
use Filefabrik\Bootraiser\BootraiserManager;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Throwable;

/**
 * Keep the same architecture with public and statics for overriding the Resolvers
 *
 * For your own Database
 */
class BootraiserDatabaseFactoryServiceProvider extends ServiceProvider
{
	/**
	 * @var Closure|null
	 */
	public static ?Closure $bootraiserFactoryResolver = null;

	/**
	 * @var Closure|null
	 */
	public static ?Closure $bootraiserModelResolver = null;

	/**
	 * @return void
	 */
	public function boot(): void
	{
		Factory::guessFactoryNamesUsing($this->factoryResolverMethod());
		Factory::guessModelNamesUsing($this->modelResolverMethod());
	}

	/**
	 * @return Closure
	 */
	protected function factoryResolverMethod(): Closure
	{
		return self::$bootraiserFactoryResolver ??= function(string $modelName) {
			$packages     = BootraiserManager::getPackages();
			$appNamespace = self::appNamespace();
			foreach ($packages as $package) {
				// todo Factory Namespace via package composer Reader
				$packageNamespace = $package->getNamespace();
				if (Str::startsWith($modelName, [$packageNamespace, $packageNamespace.'Models\\'])) {
					if ($packageNamespace === $appNamespace) {
						return $this->laravelFactoryResolverMethod()($modelName);
					}
					// PlayModel try strip model from last
					$pureModelName = Str::afterLast($modelName, '\\');

					// todo check package Namespace for Factory .current is only calculated
					return $packageNamespace.'Database\\Factories\\'.$pureModelName.'Factory';
				}
			}

			/**
			 * Call the regular Resolver if the Laravel App\\ not used with bootraiser
			 */
			return $this->laravelFactoryResolverMethod()($modelName);
		};
	}

	/**
	 * @return Closure
	 */
	protected function modelResolverMethod(): Closure
	{
		return self::$bootraiserModelResolver ??= function(Factory $factory) {
			$packages = BootraiserManager::getPackages();

			$factoryBasename           = Str::replaceLast('Factory', '', class_basename($factory));
			$namespacedFactoryBasename = Str::replaceLast(
				'Factory',
				'',
				Str::replaceFirst(Factory::$namespace, '', get_class($factory)),
			);
			$appNamespace = self::appNamespace();
			foreach ($packages as $package) {
				// todo Factory Namespace via package composer Reader
				$packageNamespace = $package->getNamespace();

				if ($packageNamespace === $appNamespace) {
					/**
					 * Call the regular Resolver if the Laravel App\\ not used with bootraiser
					 */
					// todo keep synchron with laravel abstract class Factory::modelName
					return $this->laravelModelResolverMethod()($factory);
				}

				if (Str::startsWith($namespacedFactoryBasename, [$packageNamespace])) {
					return class_exists($packageNamespace.'Models\\'.$factoryBasename)
						? $packageNamespace.'Models\\'.$factoryBasename
						: $packageNamespace.$factoryBasename;
				}
			}
			/**
			 * Call the regular Resolver if the Laravel App\\ not used with bootraiser
			 */
			// todo keep synchron with laravel abstract class Factory::modelName
			return $this->laravelModelResolverMethod()($factory);
		};
	}

	/**
	 * @return Closure
	 */
	protected function laravelFactoryResolverMethod(): Closure
	{
		return function(string $modelName) {
			$appNamespace = self::appNamespace();

			$modelName = Str::startsWith($modelName, $appNamespace.'Models\\')
				? Str::after($modelName, $appNamespace.'Models\\')
				: Str::after($modelName, $appNamespace);

			return Factory::$namespace.$modelName.'Factory';
		};
	}

	/**
	 * @return Closure
	 * @see Factory::modelName()
	 */
	protected function laravelModelResolverMethod(): Closure
	{
		return function(Factory $factory) {
			$namespacedFactoryBasename = Str::replaceLast(
				'Factory',
				'',
				Str::replaceFirst(Factory::$namespace, '', get_class($factory)),
			);

			$factoryBasename = Str::replaceLast('Factory', '', class_basename($factory));

			$appNamespace = self::appNamespace();

			return class_exists($appNamespace.'Models\\'.$namespacedFactoryBasename)
				? $appNamespace.'Models\\'.$namespacedFactoryBasename
				: $appNamespace.$factoryBasename;
		};
	}

	/**
	 * Get the application namespace for the application.
	 *
	 * @return string
	 */
	public static function appNamespace(): string
	{
		try {
			return Container::getInstance()
							->make(Application::class)
							->getNamespace()
			;
		} catch (Throwable) {
			return 'App\\';
		}
	}
}
