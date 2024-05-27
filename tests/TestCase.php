<?php declare(strict_types=1);
/**
 * PHP version 8.2
 *
 */
/** @copyright-header * */
/**
 * Testing paxsy only in standalone installation.
 * Feature testing will not work properly.
 */

namespace Filefabrik\Bootraiser\Tests;

if (class_exists(\Orchestra\Testbench\TestCase::class)) {
    abstract class TestCase extends \Orchestra\Testbench\TestCase
    {
        use TestCaseTrait;
    }
}
else {
    /**
     * Testing paxsy in a laravel installation
     * Unit Testing and Feature Testing will work
     */
    abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
    {
        use TestCaseTrait;

        // todo test in live habitat
    }
}
