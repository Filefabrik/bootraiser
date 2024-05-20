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
	 * @param PackageConfig $config
	 * @param mixed         ...$params
	 *
	 * @return void
	 */
	public function booting(PackageConfig $config, ...$params): void
	{
		$livewireDir = $config->concatPath('src/Livewire/');
		if (is_dir($livewireDir)) {
			$finder = Finder::create()
							->files()
							->in($livewireDir)
							->name('*.php')
			;

			foreach ($finder->getIterator() as $file) {
				$cls           = $file->getBasename('.php');
				$className     = $config->concatNamespace('Livewire\\'.$cls);
				$componentName = Str::of($cls)
									->explode('/')
									->filter()
									->map([Str::class, 'kebab'])
									->implode('.')
				;

				if (class_exists(Livewire::class) && class_exists($className)) {
					Livewire::component($config->getVendorPackageName().'::'.$componentName, $className);
				}
			}
		}
	}
}
