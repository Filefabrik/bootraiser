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

/**
 * @see https://laravel.com/docs/11.x/packages#migrations
 */
class Migrations implements ContractRaisilenceCore, ContractInPath, ContractOutPath
{
	use TraitPublicServiceProvider;
	use InPath;
	use OutPath;

	protected static ?string $in_path = 'database/migrations';

	// with database_path
	protected static ?string $out_path = 'migrations';

	/**
	 * If integrated on `php artisan migrate:status` these migrations will be offered and executed
	 *
	 * @return $this
	 */
	public function load(): static
	{
		$dir = $this->existingInPath();
		if ($dir) {
			// not need to publish migrations.
			// migrations are available directly from package
			$this->getPublicServiceProvider()
				 ->loadMigrationsFrom($dir)
			;
		}

		return $this;
	}

	/**
	 * Publish Migrations if need
	 * todo make prefix for published migration files
	 *
	 * @return $this
	 */
	public function publish(): static
	{
		if ($this->runningInConsole() && $dir = $this->existingInPath()) {
			$this->getPublicServiceProvider()
				 ->publishesMigrations(
				 	[$dir => database_path(self::$out_path)],
				 	$this->packageConfig->concatPackageName('migrations'),
				 )
			;
		}

		return $this;
	}
}
