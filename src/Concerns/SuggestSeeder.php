<?php declare(strict_types=1);
/**
 * PHP version 8.2
 *
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Concerns;

use Filefabrik\Bootraiser\BootraiserManager;
use Filefabrik\Bootraiser\Support\PackageConfig;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use UnexpectedValueException;
use function Laravel\Prompts\select;

class SuggestSeeder
{

    public function mainMenu(): int|string
    {
        $options = ['list_all_seeder'      => 'all seeders',
                    'default'              => 'seed default',
                    'all'                  => 'seed all main DatabaseSeeder',
                    'list_package_seeders' => 'List Package Seeders'];

        $packageCollection = new Collection($options);

        return select(label  : 'Bootraiser db:seed',
                      options: $packageCollection,
                      scroll : 10,
        );
    }

    /**
     * Single selected Seeder
     *
     * @param string $selected
     *
     * @return string|null
     */
    public function selectedSeeder(string $selected): ?string
    {
        $packageName = Str::beforeLast($selected, '-');
        $seederClass = Str::afterLast($selected, '-');

        $package = BootraiserManager::searchPackage($packageName);
        if (!$package) {
            // error
            throw new UnexpectedValueException('Bootraiser Package not found');
        }

        $packageSeeders = $this->getPackageSeeders($package);

        if ($seederClass === 'DatabaseSeeder') {
            // the main seeder "DatabaseSeeder" from a package
            $mainSeeder = $packageSeeders->get('DatabaseSeeder');

            return $mainSeeder ?: null;
        }

        // search for the class
        foreach ($packageSeeders->get('Seeders') as $seederClassNamespace) {
            if (Str::afterLast($seederClassNamespace, '\\') === $seederClass) {
                return $seederClassNamespace;
            }
        }

        return null;
    }

    public function packagesSeedersOptions()
    {
        $options = [];
        foreach ($this->listPackagesSeeders() as $packageName => $listPackagesSeeder) {
            if ($listPackagesSeeder->get('DatabaseSeeder')) {
                $k         = $packageName . '-DatabaseSeeder';
                $options[] = $k;
            }

            if ($seeders = $listPackagesSeeder->get('Seeders')) {
                foreach ($seeders as $seeder) {
                    $k         = $packageName . '-' . Str::afterLast($seeder, '\\');
                    $options[] = $k;
                }
            }
        }

        return Arr::sort($options);
    }

    /**
     * @return array<string,Collection<string,string|Collection>>
     */
    public function listPackagesSeeders(): array
    {
        $allSeeders = [];
        $pkgs       = BootraiserManager::getPackages();
        foreach ($pkgs as $packageName => $packageConfig) {
            $collection = $this->listPackageSeeders($packageName,);
            if ($collection) {
                $allSeeders[$packageConfig->getVendorPackageName()] = $collection;
            }
        }

        return $allSeeders;
    }

    protected function getPackageSeeders(PackageConfig $package): ?Collection
    {
        return $package->getConfig('trackSeeders')
                       ->isNotEmpty() ? (new Collection((new PackageSeeder($package))->findSeeder())) : null;
    }

    /**
     * @param string $packageName
     *
     * @return Collection|null
     */
    public function listPackageSeeders(string $packageName): ?Collection
    {
        return $this->getPackageSeeders(BootraiserManager::searchPackage($packageName));
    }

}
