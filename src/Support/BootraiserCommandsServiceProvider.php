<?php declare(strict_types=1);
/**
 * PHP version 8.2
 *
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Support;

use Filefabrik\Bootraiser\Console\Commands\Database\SeedCommand;
use Illuminate\Console\Application as ArtisanApplication;

use Illuminate\Support\ServiceProvider;

class BootraiserCommandsServiceProvider extends ServiceProvider
{
    /**
     * @var array<string,class-string[]>
     */
    protected array $overrides = ['command.seed' => SeedCommand::class,];

    /**
     * @return void
     */
    public function register()
    {
        if (app()->runningInConsole()) {
            $this->app->booted(function() {
                ArtisanApplication::starting(function() {
                    foreach ($this->overrides as $alias => $class_name) {
                        $this->app->singleton($alias, $class_name);
                        $this->app->singleton(get_parent_class($class_name), $class_name);
                    }
                });
            });
        }
    }
}
