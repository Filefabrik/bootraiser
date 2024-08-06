<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

use Filefabrik\Bootraiser\Concerns\Solved;

afterEach(function() {
	Solved::reset();
});
test(
	'loads 2 config files',
	function() {
		// config was injected
		expect(config('my-test.yeah'))
			->toBeTrue()
			->and(config('my-other-config.my-thats-assert-confg'))
			->toBe('my-test')
		;
	},
);
test(
	'publish 2 config files',
	function() {
		// todo check files exits now in config
		$this->artisan('vendor:publish', ['--tag' => ['default-package-config']])
			 //->expectsQuestion('Which provider or tag\'s files would you like to publish?',
							   //'<fg=gray>Tag:</> default-package-config')
			 ->assertExitCode(0)
		;
	},
);
test(
	'boot routes',
	function() {
	},
);
test(
	'load routes',
	function() {
	},
);
test(
	'boot migrations',
	function() {
	},
);

test(
	'publish routes',
	function() {
		// todo check files exits now in config
		$this->artisan('vendor:publish', ['--tag' => ['default-package-routes']])
			//->expectsQuestion('Which provider or tag\'s files would you like to publish?',
			//'<fg=gray>Tag:</> default-package-config')
			 ->assertExitCode(0)
		;
	},
);
test(
	'publish migrations',
	function() {
	},
);
test(
	'load migrations',
	function() {
	},
);
test(
	'with provider',
	function() {
	},
);
