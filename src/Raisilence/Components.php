<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Raisilence;

use Filefabrik\Bootraiser\Contracts\ContractInNamespace;
use Filefabrik\Bootraiser\Contracts\ContractInPath;
use Filefabrik\Bootraiser\Contracts\ContractRaisilenceCore;
use Filefabrik\Bootraiser\Raisilence\Support\InNamespace;
use Filefabrik\Bootraiser\Raisilence\Support\InPath;
use Filefabrik\Bootraiser\Raisilence\Support\TraitPublicServiceProvider;

/**
 * https://laravel.com/docs/11.x/packages#view-components
 */
class Components implements ContractRaisilenceCore, ContractInPath, ContractInNamespace
{
	use TraitPublicServiceProvider;
	use InPath;
	use InNamespace;

	protected static ?string $in_path = 'src/View/Components';

	protected static ?string $in_namespace = 'View\Components';

	public function load(): static
	{
		$pkg = $this->packageConfig;
		// todo configurable with and without prefix as the package is configured
		// todo with alias
		$finder   = $this->inFiles();
		$prepared = [];
		foreach (($finder ?? []) as $finderFile) {
			// extract class
			$className = $finderFile->getBasename('.php');
			$component = $pkg->concatPackageNamespace(self::$in_namespace, $className);
			if (class_exists($component)) {
				// todo with alias
				$prepared[] = $component;
			}// else wrong format of the class
		}

		if ($prepared) {
			$this->publicServiceProvider->loadViewComponentsAs($pkg->getPackageName(), $prepared);
		}

		return $this;
	}

	public function publish(): static
	{
		// not used
		return $this;
	}
}
