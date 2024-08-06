<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Contracts;

interface ContractOutPath
{
	/**
	 * @param string|null $out_path
	 *
	 * @return string|null
	 */
	public static function setOutPath(?string $out_path): ?string;
}
