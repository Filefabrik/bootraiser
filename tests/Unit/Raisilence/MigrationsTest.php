<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */
test(
	'List migration from bootraiser-testing package',
	function() {
		$this->artisan('migrate:status')
			 ->expectsOutputToContain('bootraiser-testing-migrations')
		;
	}
);
