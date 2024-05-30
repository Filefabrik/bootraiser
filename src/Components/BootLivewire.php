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
        $livewireDir = $packageConfig->concatPath('src/Livewire/');
        if (is_dir($livewireDir)) {
            $finder = Finder::create()
                            ->files()
                            ->in($livewireDir)
                            ->name('*.php')
            ;

            foreach ($finder->getIterator() as $file) {
                $cls           = $file->getBasename('.php');
                $className     = $packageConfig->concatNamespace('Livewire\\' . $cls);
                $componentName = Str::of($cls)
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
