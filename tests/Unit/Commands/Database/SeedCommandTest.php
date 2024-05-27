<?php declare(strict_types=1);
/**
 * PHP version 8.2
 *
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Tests\Filefabrik\Bootraiser\Console\Commands\Database;

test('Seed Command Test',
    function() {
    });
it('seed via bootraiser command',
    function() {
        $this->artisan('db:seed')
             ->expectsQuestion('Bootraiser db:seed', 'list_all_seeder')
             ->expectsQuestion('all seeders', 'try-command-options-EmptySeeder')
            // todo expects mre
        ;;
    });
