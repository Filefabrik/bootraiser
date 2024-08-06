<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Raisilence;

use Filefabrik\Bootraiser\Contracts\ContractInNamespace;
use Filefabrik\Bootraiser\Contracts\ContractInPath;
use Filefabrik\Bootraiser\Contracts\ContractRaisilenceCore;
use Filefabrik\Bootraiser\Packaging\Package;
use Filefabrik\Bootraiser\Raisilence\Support\InNamespace;
use Filefabrik\Bootraiser\Raisilence\Support\InPath;
use Filefabrik\Bootraiser\Raisilence\Support\TraitPublicServiceProvider;
use Filefabrik\Bootraiser\Support\Str\PathsNamespaces;
use Illuminate\Support\Str;

class Livewire implements ContractRaisilenceCore, ContractInPath, ContractInNamespace
{
	use TraitPublicServiceProvider;
	use InPath;
	use InNamespace;

	protected static ?string $in_path = 'src/Livewire';

	protected static ?string $in_namespace = 'Livewire';

	/**
	 * @return static
	 */
	public function load(): static
	{
		if (class_exists(\Livewire\Livewire::class) && $finder = $this->inFiles()) {
			foreach ($finder as $file) {
				// uncool methods, move outside
				$relDir = Str::before($file->getRelativePathname(), '.php');
				$this->makeComponentLoad($relDir);
			}
		}

		return $this;
	}

	public function publish(): static
	{
		// nothing to do, loops back
		return $this;
	}

	public function makeComponentLoad($relDir): void
	{
		$cls = PathsNamespaces::fromPathToNamespace($relDir);

		$className     = $this->packageConfig->concatPackageNamespace(self::$in_namespace, $cls);
		$componentName = self::compileLivewireComponentName($relDir);

		if (class_exists($className)) {
			\Livewire\Livewire::component(
				self::componentExpression($this->packageConfig->getPackageName(), $componentName),
				$className,
			);
		}
	}

	public static function componentExpression(Package|string $packageConfig, string $componentName): string
	{
		return (is_string($packageConfig) ? $packageConfig : $packageConfig->getPackageName()).'::'.$componentName;
	}

	/**
	 * Used also in paxsy
	 * todo move out
	 *
	 * @param string $relDir
	 *
	 * @return string
	 */
	public static function compileLivewireComponentName(string $relDir): string
	{
		return Str::of($relDir)
				  ->explode('/')
				  ->filter()
				  ->map([Str::class, 'kebab'])
				  ->implode('.')
		;
	}
}
