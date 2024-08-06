<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */
it(
	'publishes assets',
	function() {
		$this->artisan('vendor:publish', ['--tag' => 'bootraiser-testing-assets', '--ansi' => true, '--force' => true]);
	},
);
