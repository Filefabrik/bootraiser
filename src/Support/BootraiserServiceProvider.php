<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Support;

use Illuminate\Support\ServiceProvider;

class BootraiserServiceProvider extends ServiceProvider
{
	/**
	 * @return void
	 */
	public function register(): void
	{
		parent::register();
		$this
			->mergeConfigFrom(dirname(__DIR__, 2).'/config/config.php', 'bootraiser')
		;
	}

	/**
	 * @return void
	 */
	public function boot(): void
	{
		$this->publishes(
			[dirname(__DIR__, 2).'/config/config.php' => config_path('bootraiser.php')],
			'bootraiser-config',
		);
	}
}
