<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Console\Commands\Database;

use Filefabrik\Bootraiser\BootraiserManager;
use Filefabrik\Bootraiser\Concerns\PackageSeeder;
use Filefabrik\Bootraiser\Concerns\SuggestSeeders;
use Filefabrik\Bootraiser\Packaging\Package;
use Filefabrik\Bootraiser\Support\Str\Namespacering;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use function Laravel\Prompts\suggest;
use Symfony\Component\Console\Input\InputOption;
use UnexpectedValueException;

trait TraitSeedCommand
{
	public function handle(): int
	{
		if (! $this->confirmToProceed()) {
			return 1;
		}

		$callParent = false;

		$suggestSeeder = new SuggestSeeders();

		$menu = $suggestSeeder->mainMenu();

		if ('list_all_seeder' === $menu) {
			$opts = $suggestSeeder->packagesSeedersOptions();

			$selectedSeeder = suggest(
				label  : 'all seeders',
				options: fn($value) => (new Collection($opts))
					->filter(fn($title) => str_contains(Str::lower($title), Str::lower($value)))
					->all(),
				scroll : 10,
			);

			$seederPath = $suggestSeeder->selectedSeeder($selectedSeeder);

			if ($seederPath) {
				$this->input->setArgument('class', $seederPath);
				$this->info('cli command which will be called:');
				$this->info('php artisan db:seed "'.$seederPath.'"');
				$callParent = true;
			} else {
				throw new UnexpectedValueException($selectedSeeder.' seeder not found');
			}
		}
		if (! $menu) {
			$callParent = true;
		} elseif ('main' === $menu) {
			$this->input->setOption('main', true);
			$callParent = true;
		}

		return $callParent ? parent::handle() : self::FAILURE;
	}

	/**
	 * its override because of missing orchestra DatabaseSeeder
	 * Get a seeder instance from the container.
	 *
	 * @return Seeder
	 * @throws BindingResolutionException
	 */
	protected function getParentSeeder(): Seeder
	{
		$class = $this->input->getArgument('class') ?? $this->input->getOption('class');
		$class ??= '';
		if (! str_contains($class, '\\')) {
			$class = 'Database\\Seeders\\'.$class;
		}

		if (
			$class === 'Database\\Seeders\\DatabaseSeeder' &&
			! class_exists($class)
		) {
			$class = 'DatabaseSeeder';
		}
		if (class_exists($class)) {
			return $this->laravel->make($class)
								 ->setContainer($this->laravel)
								 ->setCommand($this)
			;
		}

		return new NullSeeder();
	}

	/**
	 * @throws BindingResolutionException
	 */
	protected function getSeeder(): Seeder|null
	{
		return $this->handleOverrideSeeders() ? new NullSeeder() : self::getParentSeeder();
	}

	/**
	 * the real magic, handles the packages they are under BootraiserManager registered
	 *
	 * @return bool
	 * @throws BindingResolutionException
	 */
	protected function handleOverrideSeeders(): bool
	{
		$hasMainOption = $this->option('main');

		if ($hasMainOption) {
			foreach (BootraiserManager::packages() ?? [] as $package) {
				// 1 item with a bool flag, nothing else needed
				$this->enqueueDatabaseSeeder($package);
			}

			// call th DatabaseSeeder from laravel /databases/seeders/DatabaseSeeder.php

			$this->invoker(self::getParentSeeder());

			return true;
		}

		$packageName = $this->option('package');

		if ($packageName) {
			$package = BootraiserManager::byIdentifier($packageName);

			// concrete seeder class in the Package
			$classOption = $this->option('class');
			if ($classOption) {
				$availableSubSeeders = $package->getConfig('Seeders');
				// searching the wanted seeder class in files
				$foundClass = false;
				foreach ($availableSubSeeders as $namespace) {
					// todo check concat for class option
					if (Str::endsWith(Namespacering::Divider.$classOption, $namespace)) {
						$this->seedClass($namespace);
						$foundClass = true;
					}
				}

				if (! $foundClass) {
					$this->error('Seeder '.$classOption.' not found in '.$packageName);
				}

				// has executed, no parent call
				return true;
			}

			// only the package DatabaseSeeder
			if ($package) {
				$this->setDatabaseSeeder($package);
				// else no class found, maybe miesconfigured bootraiser in a package config
			}

			return true;
		}

		// has handelt overrides, if not handle the parent command
		return false;
	}

	protected function setDatabaseSeeder(Package $package): void
	{
		$seederClass = $package->concatPackageNamespace(PackageSeeder::getInNamespace(), 'DatabaseSeeder');
		$this->seedClass($seederClass);
	}

	protected function enqueueDatabaseSeeder(Package $package): void
	{
		if (
			PackageSeeder::forPackage($package)
						 ->getDatabaseSeederPath()
		) {
			$this->setDatabaseSeeder($package);
		}
	}

	/**
	 * Invoke
	 *
	 * @param $namespacedClass
	 *

	 * @throws BindingResolutionException
	 */
	protected function seedClass($namespacedClass)
	{
		if (class_exists($namespacedClass)) {
			$this->laravel->make($namespacedClass)
								 ->setContainer($this->laravel)
								 ->setCommand($this)
			;
		}
	}

	/**
	 * @param $invocableSeeder
	 *
	 * @return void
	 */
	protected function invoker($invocableSeeder): void
	{
		// call th DatabaseSeeder from a Package
		Model::unguarded(fn() => $invocableSeeder ? $invocableSeeder->__invoke() : null);
	}

	/**
	 * @return void
	 */
	protected function configure(): void
	{
		parent::configure();

		$this->getDefinition()
			 ->addOption(
			 	new InputOption(
			 		'--package',
			 		null,
			 		InputOption::VALUE_REQUIRED,
			 		'Seed command in a package which is using bootraiser',
			 	),
			 )
		;
		$this->getDefinition()
			 ->addOption(new InputOption(
			 	'--main',
			 	null,
			 	InputOption::VALUE_OPTIONAL,
			 	'Seeds the Laravel Base application seeder and all found "DatabaseSeeder" they are tracked with bootraiser in packages',
			 ), )
		;
	}

	protected function getOptions(): array
	{
		$opts = parent::getOptions();

		foreach ($opts as $key => $opt) {
			if ($opt[0] === 'class') {
				$opts[$key] = ['class', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder', null];
			}
		}

		return $opts;
	}
}
