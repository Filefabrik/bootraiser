<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Tests\Raisilence;

use Filefabrik\Bootraiser\Raisilence\Commands;

test(
	'set in namespace',
	function() {
		Commands::setInNamespace('otherNamespace');
		expect(Commands::getInNamespace())->toBe('otherNamespace');
	}
);
