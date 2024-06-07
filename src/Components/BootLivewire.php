<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Components;

use Filefabrik\Bootraiser\Support\PackageConfig;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Symfony\Component\Finder\Finder;

/**
 * Optional Livewire
 */
class BootLivewire implements BootableComponentInterface
{
    /**
     * Booting Livewire if exists
     *
     * @param PackageConfig $packageConfig
     * @param mixed         ...$params
     *
     * @return void
     */
    public function booting(PackageConfig $packageConfig, ...$params): void
    {
        $livewireDir = $packageConfig->concatPackagePath('src/Livewire/');

        // todo make caching/bootstrapping available via PackageConfig or/and BootraiserManager to speed-up livewire loading
        // todo strategies view:cache PackageConfig BootraiserManager
        // todo for fully prod application / for mixed dev and prod / to prevent from packages they are ready developed

        if (is_dir($livewireDir)) {
            $finder = Finder::create()
                            ->files()
                            ->in($livewireDir)
                            ->name('*.php')
            ;

            foreach ($finder->getIterator() as $file) {
                // uncool methods, move outside
                $relDir        = Str::before($file->getRelativePathname(), '.php');
                $cls           = str_replace('/', '\\', $relDir);
                $className     = $packageConfig->concatPackageNamespace('Livewire', $cls);
                $componentName = Str::of($relDir)
                                    ->explode('/')
                                    ->filter()
                                    ->map([Str::class, 'kebab'])
                                    ->implode('.')
                ;

                if (class_exists(Livewire::class) && class_exists($className)) {
                    Livewire::component($packageConfig->getVendorPackageName() . '::' . $componentName, $className);
                }
            }
        }
    }
}
