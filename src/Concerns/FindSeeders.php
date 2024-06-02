<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Concerns;

use Symfony\Component\Finder\Finder;

class FindSeeders
{
	public static function databaseSeeder(string $path): ?string
	{
		$concatenatedPath = $path.'/DatabaseSeeder.php';

		return file_exists($concatenatedPath) ? $concatenatedPath : null;
	}

	/**
	 * @param string $path
	 *
	 * @return Finder|null
	 */
	public static function databaseSubSeeders(string $path): ?Finder
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
