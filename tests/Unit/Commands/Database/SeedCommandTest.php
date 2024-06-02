<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */
test(
	'Default Seed Command Test',
	function() {
		$this->artisan('db:seed')
			 ->expectsQuestion('Bootraiser db:seed', '')
		;
	}
);
it(
	'seed via bootraiser command',
	function() {
		$this->artisan('db:seed')
			 ->expectsQuestion('Bootraiser db:seed', 'list_all_seeder')
			 ->expectsQuestion('all seeders', 'try-command-options-EmptySeeder')// todo expects mre
		;
	}
);
it(
	'seed command with factory in package',
	function() {
		$this->artisan('db:seed')
			 ->expectsQuestion('Bootraiser db:seed', 'list_all_seeder')
			 ->expectsQuestion('all seeders', 'try-command-options-PlaySeeder')// todo expects mre
		;
	}
);
it(
	'seed main',
	function() {
		$this->artisan('db:seed')
			 ->expectsQuestion('Bootraiser db:seed', 'main')
//            ->expectsQuestion('all seeders', 'try-command-options-EmptySeeder')
			// todo expects mre
		;
	}
);
it(
	'seed with bootraiser',
	function() {
		$this->artisan('bootraiser:seed')
			 ->expectsQuestion('Bootraiser db:seed', 'main')
//            ->expectsQuestion('all seeders', 'try-command-options-EmptySeeder')
			// todo expects mre
		;
	}
);
