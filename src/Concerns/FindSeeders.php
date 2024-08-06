<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Concerns;

use Filefabrik\Bootraiser\Support\Str\Pathering;
use Symfony\Component\Finder\Finder;

class FindSeeders
{
	/**
	 * Main DatabaseSeeder
	 *
	 * @param string $path
	 *
	 * @return string|null
	 */
	public static function databaseSeeder(string $path): ?string
	{
		$concatenatedPath = Pathering::concat($path, 'DatabaseSeeder.php');

		return file_exists($concatenatedPath) ? $concatenatedPath : null;
	}

	/**
	 * All other Seeders
	 *
	 * @param string $path
	 *
	 * @return Finder|null
	 */
	public static function seeders(string $path): ?Finder
	{
		$finder = Finder::create()
						->files()
						->in($path)
						->notName('DatabaseSeeder.php')
						->name('*.php')
		;

		return $finder->hasResults() ? $finder : null;
	}
}
