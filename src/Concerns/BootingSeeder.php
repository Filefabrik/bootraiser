<?php declare(strict_types=1);
/**
 * PHP version 8.2
 *
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Concerns;

use Filefabrik\Bootraiser\Support\PackageConfig;

/**
 *
 */
class BootingSeeder
{

    /**
     * @param PackageConfig $packageConfig
     *
     * @return void
     */
    public function intoSeedable(PackageConfig $packageConfig)
    {
        $relDir = 'database/seeders';

        $seedersDir = $packageConfig->concatPath($relDir);
        if (app()->runningInConsole() && is_dir($seedersDir)) {
            // DatabaseSeeder is relevant for the whole Package
            $databaseSeederClass = $packageConfig->concatPath($relDir . '/DatabaseSeeder.php');
            if (is_file($databaseSeederClass)) {
                $packageConfig->ontoPool('DatabaseSeeder', true);
            }

            $databaseSubSeeder = SeederFiles::databaseSubSeeders($seedersDir);
            // Other Classes in package/database/seeder/ used for particular execution or from the DatabaseSeeder.php
            foreach ($databaseSubSeeder?->getIterator()??[] as $file) {
                $cls       = $file->getBasename('.php');
                $namespace = $packageConfig->concatNamespace('Database\\Seeders\\' . $cls);
                // all other seeders
                $packageConfig->ontoPool('Seeders', $namespace);
            }
        }
    }
}
