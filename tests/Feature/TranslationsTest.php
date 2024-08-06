<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */

/** @copyright-header * */

use Illuminate\Filesystem\Filesystem;

it(
	'from php array',
	function() {
		$translated = __('bootraiser-testing::client.client-name');
		expect($translated)->toBe('bootraiser-testing -> de -> de.json -> client-name-de FULL bootraiser-testing::client.client-name-de');
		//	with php translations only
		//expect($translated)->toBe('./packages/bootraiser-testing/lang/en/client.php -> client-name');
	},
);

it(
	'original laravel auth',
	function() {
		$translated = __('auth.failed');
		expect($translated)->toBe('\' auth failed json from package dir');
		//	with php translations only
		// 		expect($translated)->toBe('These credentials do not match our records.');
	},
);
it(
	'spookey',
	function() {
		$translated = __('spookey');
		expect($translated)->toBe('en in json');
		$translated = __('bootraiser-testing::client.spookey');

		expect($translated)->toBe('php en spookey');
	},
);

it(
	'original laravel after published auth',
	function() {
		$translated = __('bootraiser-testing::auth.failed');
		//	with php translations only
		expect($translated)->toBe('bootraiser-testing::auth.failed auth failed in package lang dir');
		// 	        expect($translated)->toBe('These credentials do not match our records.');

		$translated = __('auth.failed');
		expect($translated)->toBe('\' auth failed json from package dir');
	},
);
it(
	'published language',
	function() {
		$dir = base_path('lang/vendor/bootraiser-testing');
		$fs  = (new Filesystem());
		if ($fs->isDirectory($dir)) {
			$fs->deleteDirectories($dir);
		}

		$this->artisan('vendor:publish', ['--tag' => 'bootraiser-testing-translations', '--force' => true]);

		$expectFiles = ['de.json', 'en.json', 'en/auth.php', 'en/client.php'];

		foreach ($expectFiles as $file) {
			expect($fs->isFile($dir.'/'.$file))->toBeTrue();
		}
	},
);

it(
	'switch to german',
	function() {
		$translated = __('client.client-name-de', [], 'de');
		expect($translated)->toContain('is client name deutsch');
	},
);
