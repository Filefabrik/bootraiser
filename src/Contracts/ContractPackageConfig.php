<?php

declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Contracts;

interface ContractPackageConfig
{
	/**
	 * Package-Name (without the vendor)
	 */
	public function getPackageName(): string;

	/**
	 * Safe concat package paths
	 *
	 * @param  string  $path
	 */
	public function concatPackagePath(...$path): string;
}
