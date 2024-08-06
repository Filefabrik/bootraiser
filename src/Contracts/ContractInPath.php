<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Contracts;

use Symfony\Component\Finder\Finder;

interface ContractInPath
{
	/**
	 * @return string|null
	 */
	public function existingInPath(): ?string;

	/**
	 * All files for the type
	 */
	public function inFiles(): ?Finder;
}
