<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Providers;

use Filefabrik\Bootraiser\Concerns\PublicServiceProvider;
use Illuminate\Support\ServiceProvider;

class BootraiserServiceProvider extends ServiceProvider
{
	public function __construct($app)
	{
		parent::__construct($app);
		/**
		 * Main Map Loader is need.
		 * todo not the right way
		 */
	}

	/**
	 * @return void
	 */
	public function register(): void
	{
		parent::register();
		PublicServiceProvider::with($this)
							 ->mergeConfigFrom(
							 	dirname(__DIR__, 2).'/config/bootraiser.php',
							 	'bootraiser',
							 )
		;
	}

	/**
	 * @return void
	 */
	public function boot(): void
	{
		// publishes all
		// todo make Raiser-Class as Trait Provider to reduce the footprint in boot
		PublicServiceProvider::with($this)
							 ->publishes(['bootraiser.php' => config_path('bootraiser.php')], 'bootraiser-config')
		;
	}
}
