<?php declare(strict_types=1);
/**
 * PHP version 8.2
 *
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Concerns;

use Symfony\Component\Finder\Finder;

class SeederFiles
{

    public static function databaseSeeder(string $path): ?string
    {
        $concatenatedPath = $path . '/DatabaseSeeder.php';

        return file_exists($concatenatedPath) ? $concatenatedPath : null;
    }

    public static function databaseSubSeeders(string $path)
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
