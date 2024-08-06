<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Concerns;

use Filefabrik\Bootraiser\Contracts\ContractInNamespace;
use Filefabrik\Bootraiser\Contracts\ContractInPath;
use Filefabrik\Bootraiser\Packaging\Package;
use Filefabrik\Bootraiser\Raisilence\Support\InNamespace;
use Filefabrik\Bootraiser\Raisilence\Support\InPath;

class PackageSeeder implements ContractInPath, ContractInNamespace
{
	use InPath;
	use InNamespace;

	/**
	 * @var ?string
	 */
	protected static ?string $in_path = 'database/seeders';

	protected static ?string $in_namespace = 'Database\Seeders';

	/**
	 * @param Package $packageConfig
	 */
	protected function __construct(readonly protected Package $packageConfig)
	{
	}

	public static function forPackage(Package $packageConfig): self
	{
		return new self($packageConfig);
	}

	/**
	 * @return array{DatabaseSeeder: string|null, Seeders: array<int,string>|null}
	 */
	public function findSeeder(): array
	{
		$seeders = [];

		// DatabaseSeeder is relevant for the whole Package
		$seeders['DatabaseSeeder'] = $this->getDatabaseSeederPath();

		// sub seeders
		$seeders['Seeders'] = $this->getSeeders();

		return $seeders;
	}

	public function getDatabaseSeederNamespace()
	{
		return $this->packageConfig->concatPackageNamespace(self::$in_namespace, 'DatabaseSeeder');
	}

	/**
	 * @return string|null
	 */
	public function getDatabaseSeederPath(): ?string
	{
		return ($existingPath = $this->existingInPath()) ? FindSeeders::databaseSeeder($existingPath) : null;
	}

	/**
	 * @return array|null
	 */
	public function getSeeders(): ?array
	{
		$path = $this->existingInPath();
		if ($path) {
			$seeders           = [];
			$databaseSubSeeder = FindSeeders::seeders($path);
			// Other Classes in package/database/seeder/ used for particular execution or from the DatabaseSeeder.php
			foreach ($databaseSubSeeder?->getIterator() ?? [] as $file) {
				// todo check is seeder class
				$cls       = $file->getBasename('.php');
				$namespace = $this->packageConfig->concatPackageNamespace(self::$in_namespace, $cls);
				// all other seeders
				$seeders[] = $namespace;
			}

			return $seeders;
		}

		return null;
	}
}
