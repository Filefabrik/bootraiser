<?php
declare(strict_types=1);
/**
 * PHP version 8.2
 *
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Tests\Concerns;

use Filefabrik\Bootraiser\Concerns\SuggestSeeders;

test('Suggest seeder',
    function() {
        $sSeeder = new SuggestSeeders();
        $r       = $sSeeder->listPackagesSeeders();

        expect($r)->toBeArray();
    });
