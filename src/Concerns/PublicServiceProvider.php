<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Concerns;

use BadMethodCallException;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * prevents from Traits and invokes the ServiceProvider Methods
 *
 * @see  ServiceProvider::mergeConfigFrom()
 * @method self mergeConfigFrom($path, $key)
 *
 * @see  ServiceProvider::loadRoutesFrom()
 * @method self loadRoutesFrom($path)
 *
 * @see  ServiceProvider::loadViewsFrom()
 * @method self loadViewsFrom($path, $namespace)
 *
 * @see  ServiceProvider::loadViewComponentsAs()
 * @method self loadViewComponentsAs($prefix, array $components)
 *
 * @see  ServiceProvider::loadViewsFrom()
 * @method self loadTranslationsFrom($path, $namespace)
 *
 * @see  ServiceProvider::loadJsonTranslationsFrom()
 * @method self loadJsonTranslationsFrom($path)
 *
 * @see  ServiceProvider::loadMigrationsFrom()
 * @method self loadMigrationsFrom($paths)
 *
 * todo not used yet
 * @see  ServiceProvider::loadFactoriesFrom()
 * @method self loadFactoriesFrom($paths)
 *
 * @see  ServiceProvider::publishesMigrations()
 * @method self publishesMigrations(array $paths, $groups = null)
 *
 * @see  ServiceProvider::publishes()
 * @method self publishes(array $paths, $groups = null)
 */
readonly class PublicServiceProvider
{
	/**
	 * @param ServiceProvider $serviceProvider
	 */
	public function __construct(private ServiceProvider $serviceProvider)
	{
	}

	/**
	 * Instance call
	 *
	 * @param ServiceProvider $serviceProvider
	 *
	 * @return self
	 */
	public static function with(ServiceProvider $serviceProvider): self
	{
		return new self($serviceProvider);
	}

	/**
	 * @param $method
	 * @param $parameters
	 *
	 * @return $this
	 * @throws ReflectionException
	 */
	public function __call($method, $parameters): self
	{
		$this->makePublic($method)
			 ->invokeArgs($this->serviceProvider, (array) $parameters)
		;

		return $this;
	}

	/**
	 * @throws ReflectionException
	 */
	protected function makePublic(string $method): ReflectionMethod
	{
		if (! method_exists($this->serviceProvider, $method)) {
			throw new BadMethodCallException(sprintf(
				'Method %s does not exist in class "%s".',
				$method,
				get_class($this->serviceProvider),
			));
		}

		return (new ReflectionClass($this->serviceProvider))->getMethod($method);
	}

	/**
	 * @return ServiceProvider
	 */
	public function getServiceProvider(): ServiceProvider
	{
		return $this->serviceProvider;
	}
}
