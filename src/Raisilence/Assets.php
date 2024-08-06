<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Raisilence;

use Filefabrik\Bootraiser\Contracts\ContractInPath;
use Filefabrik\Bootraiser\Contracts\ContractRaisilenceCore;
use Filefabrik\Bootraiser\Raisilence\Support\InPath;
use Filefabrik\Bootraiser\Raisilence\Support\TraitPublicServiceProvider;

/**
 * @see https://laravel.com/docs/11.x/packages#public-assets
 */
class Assets implements ContractRaisilenceCore, ContractInPath
{
	use TraitPublicServiceProvider;
	use InPath;

	/**
	 * Locating segments to read a package component
	 * todo configurable in
	 *
	 * @var string|null
	 */
	protected static ?string $in_path = 'public';

	public function load(): static
	{
		// nothing here
		return $this;
	}

	public function publish(): static
	{
		if ($this->runningInConsole() && $dir = $this->existingInPath()) {
			$target = 'vendor/'.$this->packageConfig->getPackageName();
			$this->publicServiceProvider->publishes(
				[$dir => public_path($target)],
				$this->packageConfig->concatPackageName('assets'),
			);
		}

		return $this;
	}
}
