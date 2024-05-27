<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */

namespace Filefabrik\Bootraiser\Tests;

use App\Providers\AppServiceProvider;
use DemoPackage\TryCommandOptions\Providers\TryCommandOptionsServiceProvider;
use MyCompany\TheCounter\Providers\TheCounterServiceProvider;

/** @copyright-header * */

/**
 * bundling all Test-Case Methods that they are usable in package-test context and in laravel "live" context testable
 */
trait TestCaseTrait
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
            AppServiceProvider::class,
            TheCounterServiceProvider::class,
            TryCommandOptionsServiceProvider::class,
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
