<?php declare(strict_types=1);
/**
 * PHP version 8.2
 *
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Concerns;

use Filefabrik\Bootraiser\BootraiserManager;
use Filefabrik\Bootraiser\Support\PackageConfig;
use Illuminate\Support\Collection;
use function Laravel\Prompts\select;

class SuggestSeeder
{
    public function __construct(private readonly \Illuminate\Console\Command $command)
    {
    }

    public function mainMenu()
    {
        $options           = ['list_all_seeder'      => 'all seeders',
                              'default'              => 'seed default',
                              'all'                  => 'seed all main DatabaseSeeder',
                              'list_package_seeders' => 'List Package Seeders'];
        $packageCollection = new Collection($options);

        return select(label  : 'Bootraiser db:seed',
                      options: $packageCollection,
                      scroll : 10,
        );
    }

    public function listPackagesSeeders()
    {
        $allSeeders = [];
        $pkgs       = BootraiserManager::getPackages();
        foreach ($pkgs as $packageName => $packageConfig) {
            $collection = $this->listPackageSeeders($packageName,);
            if ($collection) {
                $allSeeders[$packageName] = $collection;
            }
        }

        return $allSeeders;
    }

    public function listPackageSeeders(string $packageName, null|PackageConfig $package = null)
    {
        $package ??= BootraiserManager::searchPackage($packageName);

        $seeders = [];
        if ($package) {
            // main seeder
            if (count($package->getPool('DatabaseSeeder'))) {
                $seederClass               = $package->concatNamespace('Database\Seeders\DatabaseSeeder');

                $seeders['DatabaseSeeder'] = $seederClass;
            }

            // sub seeders
            $availableSubSeeders = $package->getPool('Seeders');
            $seeders['Seeders']  = $availableSubSeeders;

            return new Collection($seeders);
        }

        return null;
    }

}
