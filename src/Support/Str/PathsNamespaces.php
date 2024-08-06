<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Support\Str;

class PathsNamespaces
{
	public static function trim(string $s): string
	{
		return Pathering::trim(Namespacering::trim($s));
	}

	public static function ltrim(string $s): string
	{
		return Pathering::ltrim(Namespacering::ltrim($s));
	}

	public static function fromPathToNamespace(string $s): string
	{
		return (string) str_replace(Pathering::Divider, Namespacering::Divider, $s);
	}

	public static function fromNamespaceToPath(string $s): string
	{
		return (string) str_replace(Namespacering::Divider, Pathering::Divider, $s);
	}
}
