<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */
/**
 * Testing paxsy only in standalone installation.
 * Feature testing will not work properly.
 */

namespace Filefabrik\Bootraiser\Tests;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
	/**
	 * @return void
	 */
	protected function tearDown(): void
	{
		parent::tearDown();
	}

	/**
	 * @param $app
	 *
	 * @return string[]
	 */
	protected function getPackageProviders($app): array
	{
		return [
		];
	}

	/**
	 * @param $app
	 *
	 * @return class-string[]
	 */
	protected function getPackageAliases($app): array
	{
		// todo rename to packages
		return [
		];
	}
}
