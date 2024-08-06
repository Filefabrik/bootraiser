<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */

/** @copyright-header * */

use Filefabrik\Bootraiser\Support\Str\PathsNamespaces;

test(
	'from namespace to path',
	function($val, $ex) {
		expect(PathsNamespaces::fromNamespaceToPath($val))->toBe($ex);
	}
)->with([
	["t\s\s", 't/s/s'],
	[Filefabrik\Bootraiser\Support\Str::class, 'Filefabrik/Bootraiser/Support/Str'],
]);
test(
	'ltrim',
	function() {
	}
);
test(
	'trim',
	function() {
	}
);
test(
	'from path to namespace',
	function() {
	}
);
