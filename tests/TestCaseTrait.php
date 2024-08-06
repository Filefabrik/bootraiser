<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Tests;

use Filefabrik\Bootraiser\Concerns\Solved;
use Filefabrik\Bootraiser\Providers\BootraiserCommandsServiceProvider;
use Filefabrik\Bootraiser\Providers\BootraiserDatabaseFactoryServiceProvider;
use Filefabrik\Bootraiser\Providers\BootraiserServiceProvider;
use Filefabrik\BootraiserTesting\Providers\BootraiserTestingServiceProvider;
use Livewire\LivewireServiceProvider;

/**
 * bundling all Test-Case Methods that they are usable in package-test context and in laravel "live" context testable
 */
trait TestCaseTrait
{
	protected function tearDown(): void
	{
		parent::tearDown();
	}

	protected function setUp(): void
	{
		parent::setUp();
		Solved::reset();
	}

	/**
	 * @return string[]
	 */
	protected function getPackageProviders($app): array
	{
		return [
			BootraiserServiceProvider::class,
			BootraiserCommandsServiceProvider::class,
			BootraiserDatabaseFactoryServiceProvider::class,
			LivewireServiceProvider::class,
			BootraiserTestingServiceProvider::class,
		];
	}

	/**
	 * @return class-string[]
	 */
	protected function getPackageAliases($app): array
	{
		return [
		];
	}
}
