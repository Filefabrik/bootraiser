<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Raisilence\Support;

use Filefabrik\Bootraiser\Concerns\PublicServiceProvider;
use Filefabrik\Bootraiser\Packaging\Package;

trait TraitPublicServiceProvider
{
	/**
	 * @param PublicServiceProvider $publicServiceProvider
	 * @param Package               $packageConfig
	 */
	public function __construct(
		protected readonly PublicServiceProvider $publicServiceProvider,
		private readonly Package $packageConfig,
	) {
	}

	public function getPublicServiceProvider(): PublicServiceProvider
	{
		return $this->publicServiceProvider;
	}

	public function boot(): static
	{
		return $this->load()
					->publish()
		;
	}

	// calling app() method in bootraiser only on time
	protected function runningInConsole(): bool
	{
		return app()->runningInConsole();
	}
}
