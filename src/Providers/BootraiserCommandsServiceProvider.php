<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Providers;

use Filefabrik\Bootraiser\Console\Commands\Database\BootraiserSeedCommand;
use Filefabrik\Bootraiser\Console\Commands\Database\SeedCommand;
use Illuminate\Console\Application as ArtisanApplication;
use Illuminate\Support\ServiceProvider;

class BootraiserCommandsServiceProvider extends ServiceProvider
{
	/**
	 * @var array<string,class-string>
	 */
	protected static array $overrides = ['command.seed' => SeedCommand::class];

	/**
	 * @return void
	 */
	public function boot(): void
	{
		if (app()->runningInConsole()) {
			// todo documentation
			if (config('bootraiser.replace_command')) {
				self::enableReplaceSeedCommand();
			}
			// the regular bootraiser:seed command
			$this->commands([BootraiserSeedCommand::class]);
		}
	}

	// helper
	public static function enableReplaceSeedCommand(): void
	{
		app()->booted(function() {
			ArtisanApplication::starting(function() {
				foreach (self::$overrides as $alias => $class_name) {
					app()->singleton($alias, $class_name);
					app()->singleton(get_parent_class($class_name), $class_name);
				}
			});
		});
	}
}
