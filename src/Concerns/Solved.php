<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Concerns;

use Illuminate\Support\Facades\App;

/**
 * Prevent from load or boot again
 */
class Solved
{
	public static array $solved = [];

	public static function setSolved(...$keys): void
	{
		self::$solved[self::getKey(...$keys)] ??= true;
	}

	public static function isSolved(...$keys): bool
	{
		if (App::runningUnitTests()) {
			return false;
		}

		return self::$solved[self::getKey(...$keys)] ?? false;
	}

	public static function reset(...$keys): void
	{
		$key = $keys ? self::getKey(...$keys) : null;
		if ($key) {
			foreach (self::$solved as $solvedKey => $value) {
				if (str_starts_with($solvedKey, $key)) {
					unset(self::$solved[$solvedKey]);
				}
			}
		} else {
			self::$solved = [];
		}
	}

	protected static function getKey(...$keys): string
	{
		return implode('::', ...$keys);
	}
}
