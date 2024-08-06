<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Tests\Console\Commands\Database;

it(
	'bootraiser seed',
	function() {
		//$this->artisan('migrate',[]);
		$this->artisan('bootraiser:seed', ['--package' => 'bootraiser-testing'])
			 ->expectsQuestion('Bootraiser db:seed', '')
		;
	}
);
