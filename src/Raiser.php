<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser;

use BadMethodCallException;
use Exception;
use Filefabrik\Bootraiser\Concerns\PublicServiceProvider;
use Filefabrik\Bootraiser\Concerns\Solved;
use Filefabrik\Bootraiser\Contracts\ContractRaisilenceCore;
use Filefabrik\Bootraiser\Facades\Mapper;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use UnexpectedValueException;

/**
 * Raiser
 * generell boot{Type} publishes and load, in clear words: do all with the type
 * selective publish{Type} allows via the publish command
 * selective load{Type} loads the Component into laravel
 * @method self all()
 * @method self Migrations() // publish and integrate
 * @method self publishMigrations()
 * @method self loadMigrations()
 * @method self Routes() // publish and integrate
 * @method self loadRoutes()
 * @method self publishRoutes()
 * @method self Configs()
 * @method self loadConfigs()
 * @method self publishConfigs()
 * @method self Translations() // publish and load
 * @method self loadTranslations()
 * @method self publishTranslations()
 * @method self Views() // publish and load
 * @method self loadViews()
 * @method self publishViews()
 * @method self Components()// load
 * @method self loadComponents()
 * @method self Commands() // load
 * @method self loadCommands()
 * @method self Livewire() // load
 * @method self loadLivewire()
 * @method self Assets()
 * @method self publishAssets()
 */
class Raiser
{
	protected null|PublicServiceProvider $publicServiceProvider;

	protected function __construct(private readonly ServiceProvider|null $serviceProvider)
	{
	}

	protected function getPublicServiceProvider(): PublicServiceProvider
	{
		return $this->publicServiceProvider ??= PublicServiceProvider::with($this->serviceProvider);
	}

	public static function forProvider(ServiceProvider $provider): self
	{
		return new self($provider);
	}

	/**
	 * todo can be moved outside
	 *
	 * @param array $raisers
	 *
	 * @return $this
	 */
	public function runFromArray(array $raisers): static
	{
		// if global raisers, inject it
		foreach ($raisers as $command => $arguments) {
			if (is_int($command)) {
				//  add arguments
				$this->runRaiser($arguments);
			} else {
				// config was given within the config array

				//  merge arguments
				$this->runRaiser($command, ...$arguments);
			}
		}

		return $this;
	}

	/**
	 * With direct arguments
	 *
	 * @param $command
	 * @param ...$arguments
	 *
	 * @return $this
	 */
	protected function runRaiser($command, ...$arguments): static
	{
		$this->{$command}(...$arguments);

		return $this;
	}

	/**
	 * @param $name
	 * @param $arguments
	 *
	 * @return self
	 * @throws Exception
	 */
	public function __call($name, $arguments): self
	{
		if (strtolower($name) === 'all') {
			return $this->runFromArray(array_keys(Mapper::getMapper()));
		}

		// todo last part is All so make iterable call
		// todo extract arguments from config and merge them
		$callableClass = $this->getClassMap($name);
		if ($callableClass) {
			// only the keywords such as 'Migrations' makes

			$method = 'boot';
		} else {
			// todo extract keys from Map as an other way to configure a service "configs" "migrations"
			[$classType, $method] = $this->extractName($name);

			// boot|handle the type

			$callableClass = $this->classFromMap($classType);
		}

		$packageConfig = BootraiserManager::getPackageConfig($this->getPublicServiceProvider()
																  ->getServiceProvider());
		$packageName = $packageConfig->getPackageName();

		$solvableParams = [$packageName, $callableClass, $method];
		// todo extra method to enable or disable solver (optional if need)
		if (! Solved::isSolved($solvableParams)) {
			$instance = new $callableClass(
				$this->getPublicServiceProvider(),
				$packageConfig,
			);

			Solved::setSolved($solvableParams);

			$instance->{$method}();
		}

		/* else {
			 $ln = sprintf('already solved "%s"', implode('::', $solvableParams));
			 Log::info($ln);
		 }*/

		return $this;
	}

	/**
	 * @param string $name
	 *
	 * @return array
	 */
	protected function extractName(string $name): array
	{
		foreach (['boot', 'publish', 'load'] as $method) {
			if (Str::startsWith($name, $method)) {
				$class = Str::replaceFirst($method, '', $name);

				return [$class, $method];
			}
		}
		throw new BadMethodCallException(sprintf('Method "%s" can not be called', $name));
	}

	/**
	 * @param string $classType
	 *
	 * @return class-string<ContractRaisilenceCore>
	 */
	protected function classFromMap(string $classType): string
	{
		$callableClass = $this->getClassMap($classType);

		return $callableClass
			??
			throw new UnexpectedValueException(sprintf(
				'Class "%s" from "%s" to raise a booting-service was not found|mapped in configs/bootraiser.php',
				$classType,
				$this->serviceProvider::class,
			));
	}

	/**
	 * Mapping is static class
	 *
	 * @param string $classType
	 * todo implement adding other bootservice or replace core bootservices
	 *
	 * @return class-string<ContractRaisilenceCore>|null
	 * @see Mapper
	 */
	protected function getClassMap(string $classType): ?string
	{
		return Mapper::getMapper()[ucfirst($classType)] ?? null;
	}
}
