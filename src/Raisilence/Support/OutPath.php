<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header **/

namespace Filefabrik\Bootraiser\Raisilence\Support;

trait OutPath
{
	/**
	 * @param string|null $out_path
	 *
	 * @return string|null
	 */
	public static function setOutPath(?string $out_path): ?string
	{
		return self::$out_path = $out_path;
	}
}
