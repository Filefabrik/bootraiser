<?php

declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Contracts;

use Filefabrik\Bootraiser\Concerns\PublicServiceProvider;
use Filefabrik\Bootraiser\Packaging\Package;

interface ContractRaisilenceCore
{
	public function __construct(PublicServiceProvider $publicServiceProvider, Package $packageConfig);

	public function getPublicServiceProvider(): PublicServiceProvider;

	/**
	 * @return static
	 */
	public function boot(): static;

	/**
	 * @return static
	 */
	public function load(): static;

	/**
	 * @return static
	 */
	public function publish(): static;
}
