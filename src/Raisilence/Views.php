<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Raisilence;

use Filefabrik\Bootraiser\Contracts\ContractInPath;
use Filefabrik\Bootraiser\Contracts\ContractOutPath;
use Filefabrik\Bootraiser\Contracts\ContractRaisilenceCore;
use Filefabrik\Bootraiser\Raisilence\Support\InPath;
use Filefabrik\Bootraiser\Raisilence\Support\OutPath;
use Filefabrik\Bootraiser\Raisilence\Support\TraitPublicServiceProvider;
use Filefabrik\Bootraiser\Support\Str\Pathering;

/**
 * https://laravel.com/docs/11.x/packages#views
 */
class Views implements ContractRaisilenceCore, ContractInPath, ContractOutPath
{
	use TraitPublicServiceProvider;
	use InPath;
	use OutPath;

	protected static ?string $in_path = 'resources/views';

	protected static ?string $out_path = 'views/vendor';

	public function load(): static
	{
		$viewsDir = $this->existingInPath();

		if ($viewsDir) {
			$packageName = $this->packageConfig->getPackageName();
			$this->publicServiceProvider->loadViewsFrom($viewsDir, $packageName);
		}

		return $this;
	}

	public function publish(): static
	{
		if ($this->runningInConsole() && $dir = $this->existingInPath()) {
			$this->publicServiceProvider->publishes(
				[$dir => resource_path(Pathering::concat(
					self::$out_path,
					// todo must match with the package name in view
					$this->packageConfig->getPackageName(),
				))],
				$this->packageConfig->concatPackageName('views'),
			);
		}

		return $this;
	}
}
