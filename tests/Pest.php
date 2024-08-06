<?php declare(strict_types=1);

use Filefabrik\Bootraiser\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class)->in('Unit', 'Feature');
