<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */

/** @copyright-header * */

use Filefabrik\Bootraiser\Concerns\Solved;
use Filefabrik\Bootraiser\Providers\BootraiserCommandsServiceProvider;

beforeAll(function() {
	//$bp=app()->basePath()    ;
});
// todo spin a override config before bootraiser testing
beforeEach(function() {
	config()->set('bootraiser.replace_command', true);
});
afterEach(function() {
	Solved::reset();
});
it(
	'Default Seed Command Test',
	function() {
		BootraiserCommandsServiceProvider::enableReplaceSeedCommand();
		$this->artisan('db:seed')
			 ->expectsQuestion('Bootraiser db:seed', '')
		;
	},
);
it(
	'seed via bootraiser command',
	function() {
		$this->artisan('bootraiser:seed')
			 ->expectsQuestion('Bootraiser db:seed', 'list_all_seeder')
			 ->expectsQuestion('all seeders', 'bootraiser-testing-BettySeeder')
		;
		// todo expects more
	},
);
it(
	'seed command with factory in package',
	function() {
		BootraiserCommandsServiceProvider::enableReplaceSeedCommand();
		$this->artisan('db:seed')
			 ->expectsQuestion('Bootraiser db:seed', 'list_all_seeder')
			 ->expectsQuestion('all seeders', 'bootraiser-testing-AllySeeder')
		;
		// todo expects more
	},
);
it(
	'seed main',
	function() {
		BootraiserCommandsServiceProvider::enableReplaceSeedCommand();
		$this->artisan('db:seed')
			 ->expectsQuestion('Bootraiser db:seed', 'main')
		;
		//            ->expectsQuestion('all seeders', 'try-command-options-EmptySeeder')
		// todo expects more
	},
);
it(
	'seed with bootraiser',
	function() {
		BootraiserCommandsServiceProvider::enableReplaceSeedCommand();
		$this->artisan('bootraiser:seed')
			 ->expectsQuestion('Bootraiser db:seed', 'main')
		;
		//            ->expectsQuestion('all seeders', 'try-command-options-EmptySeeder')
		// todo expects more
	},
);
