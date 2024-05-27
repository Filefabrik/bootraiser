<?php
declare(strict_types=1);
/**
 * PHP version 8.2
 *
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Tests\Concerns;

use Filefabrik\Bootraiser\Concerns\SuggestSeeder;

test('Suggest seeder',
    function() {
        $sSeeder = new SuggestSeeder();
        $r       = $sSeeder->listPackagesSeeders();

        expect($r)->toBeArray();
    });
