<?php declare(strict_types=1);
/**
 * PHP version 8.2
 *
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Support;

use Illuminate\Support\ServiceProvider;

class BootraiserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this
            ->mergeConfigFrom(dirname(__DIR__,2).'/config/config.php', 'bootraiser')
        ;
    }

    public function boot()
    {
        $this->publishes(
            [dirname(__DIR__,2).'/config/config.php' => config_path('bootraiser.php')],
            'bootraiser-config',
        );
    }
}