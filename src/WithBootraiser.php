<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser;

use Illuminate\Support\Arr;

/**
 * fill in each service provider
 */
trait WithBootraiser
{
	use Bootraiser;

	protected function bootraiserRegister(...$parts): void
	{
		$flat = Arr::flatten([...$parts]);
		$this->registerBootraiserServices($flat);
	}

	protected function bootraiserBoot(...$parts): void
	{
		$flat = Arr::flatten([...$parts]);
		$this->bootBootraiserServices($flat);
	}

	protected function bootraiserIntegrate(...$parts): void
	{
		$flat = Arr::flatten([...$parts]);
		$this->integrateBootraiserServices($flat);
	}
}
