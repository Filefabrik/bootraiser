<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Console\Commands\Database;

use Filefabrik\Bootraiser\BootraiserManager;
use Filefabrik\Bootraiser\Concerns\SuggestSeeders;
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
			$opts           = $suggestSeeder->packagesSeedersOptions();
			$selectedSeeder = suggest(
				label  : 'all seeders',
				options: fn($value) => (new Collection($opts ?? []))
					->filter(fn($title) => str_contains(Str::lower($title), Str::lower($value)))
					->all(),
				scroll : 10
			);

			$classToSeed = $suggestSeeder->selectedSeeder($selectedSeeder);

			if ($classToSeed) {
				$this->input->setArgument('class', $classToSeed);
				$this->info('cli command which will be called:');
				$this->info('php artisan db:seed "'.$classToSeed.'"');
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
	 * @throws BindingResolutionException
	 */
	protected function getSeeder(): Seeder|null
	{
		return $this->handleOverrideSeeders() ? null : parent::getSeeder();
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
			$packages = BootraiserManager::packages();

			$suggest = new SuggestSeeders();
			if (count($packages)) {
				foreach ($packages as $package) {
					// 1 item with a bool flag, nothing else needed
					$packageSeeders = $suggest->getPackageSeeders($package);

					if ($packageSeeders->get('DatabaseSeeder')) {
						$seederClass = $package->concatPackageNamespace('Database\Seeders\DatabaseSeeder');

						$this->seedClass($seederClass);
					}
				}
			}

			if ($invocableSeeder = parent::getSeeder()) {
				// call th DatabaseSeeder from laravel /databases/seeders/DatabaseSeeder.php
				$this->invoker($invocableSeeder);
			}

			return true;
		}

		$packageName = $this->option('package');

		if ($packageName) {
			$package = BootraiserManager::searchPackage($packageName);

			// try sub-seeder
			$classOption = $this->option('class');
			if ($classOption) {
				$availableSubSeeders = $package->getConfig('Seeders');
				// searching the wanted seeder class in files
				$foundClass = false;
				foreach ($availableSubSeeders as $namespace) {
                    // todo check concat for class option
					if (Str::endsWith('\\'.$classOption, $namespace)) {
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
			if ($package && count($package->getConfig('DatabaseSeeder'))) {
				$seederClass = $package->concatPackageNamespace('Database\Seeders\DatabaseSeeder');
				if (class_exists($seederClass)) {
					$this->seedClass($seederClass);
				}
				// else no class found, maybe miesconfigured bootraiser in a package config
			}

			return true;
		}

		// has handelt overrides, if not handle the parent command
		return false;
	}

	/**
	 * Invoke
	 *
	 * @param $namespacedClass
	 *
	 * @return void
	 * @throws BindingResolutionException
	 */
	protected function seedClass($namespacedClass): void
	{
		$invocableSeeder = $this->laravel->make($namespacedClass)
										 ->setContainer($this->laravel)
										 ->setCommand($this)
		;

		$this->invoker($invocableSeeder);
	}

	/**
	 * @param $invocableSeeder
	 *
	 * @return void
	 */
	protected function invoker($invocableSeeder): void
	{
		// call th DatabaseSeeder from a Package
		Model::unguarded(fn() => $invocableSeeder->__invoke());
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
}
