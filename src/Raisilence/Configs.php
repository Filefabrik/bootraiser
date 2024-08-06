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
 * @see https://laravel.com/docs/11.x/packages#default-package-configuration
 * @see https://laravel.com/docs/11.x/packages#publishing-file-groups
 */
class Configs implements ContractRaisilenceCore, ContractInPath
{
	use TraitPublicServiceProvider;
	use InPath;

	protected static string $in_path = 'config';

	/**
	 * @see https://laravel.com/docs/11.x/packages#default-package-configuration
	 */
	public function load(): static
	{
		// atm the default key-value
		$handle = $this->all() ?? [];
		foreach ($handle as [$file, $key]) {
			$this->getPublicServiceProvider()
				 ->mergeConfigFrom($file, $key)
			;
		}

		return $this;
	}

	/**
	 * publish the config
	 *
	 * @see https://laravel.com/docs/11.x/packages#publishing-file-groups
	 *
	 *
	 * @return $this
	 */
	public function publish(): static
	{
		if ($this->runningInConsole()) {
			$prepared = [];
			foreach ($this->all() ?? [] as [$file, $key]) {
				$fname           = pathinfo($file, PATHINFO_BASENAME);
				$prepared[$file] = config_path($fname);
			}
			// todo handle tag:key
			if ($prepared) {
				$this->getPublicServiceProvider()
					 ->publishes($prepared, $this->packageConfig->concatPackageName('configs'))
				;
			}
		}

		return $this;
	}

	protected function all(): ?array
	{
		$finder = $this->inFiles();
		if ($finder?->hasResults()) {
			$files = [];
			foreach ($finder as $file) {
				// todo move format file is key
				$files[] = [$file->getRealPath(), $file->getBasename('.php')];
			}

			return $files;
		}

		return null;
	}
}
