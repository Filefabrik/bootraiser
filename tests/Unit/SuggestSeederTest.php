<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

use Filefabrik\Bootraiser\Concerns\SuggestSeeders;

it(
	'Suggest seeder',
	function() {
		$sSeeder = new SuggestSeeders();
		$r       = $sSeeder->listPackagesSeeders();

		expect($r)->toBeArray();
	}
);
