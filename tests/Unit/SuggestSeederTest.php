<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

use Filefabrik\Bootraiser\Concerns\Solved;
use Filefabrik\Bootraiser\Concerns\SuggestSeeders;

afterEach(function() {
	Solved::reset();
});
it(
	'Suggest seeder',
	function() {
		$sSeeder = new SuggestSeeders();
		$r       = $sSeeder->listPackagesSeeders();

		expect($r)->toBeArray();
	}
);
