<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

use Filefabrik\Bootraiser\Concerns\Solved;

afterEach(function() {
	Solved::reset();
});
it(
	'works',
	function() {
		expect(true)->toBeTrue();
	}
);
