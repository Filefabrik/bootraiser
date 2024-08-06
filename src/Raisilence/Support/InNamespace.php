<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header **/

namespace Filefabrik\Bootraiser\Raisilence\Support;

trait InNamespace
{
	/**
	 * Segments only called class have
	 *
	 * @param string|null $in_namespace
	 *
	 * @return string|null
	 */
	public static function setInNamespace(?string $in_namespace): ?string
	{
		return self::$in_namespace = $in_namespace;
	}

	/**
	 * @return string|null
	 */
	public static function getInNamespace(): ?string
	{
		return self::$in_namespace;
	}
}
