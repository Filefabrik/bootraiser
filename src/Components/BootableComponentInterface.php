<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Components;

use Filefabrik\Bootraiser\Support\PackageConfig;

interface BootableComponentInterface
{
	public function booting(PackageConfig $packageConfig, ...$params);
}
