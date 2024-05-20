<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Support;

use Filefabrik\Bootraiser\Components\BootableComponentInterface;
use Illuminate\Support\Str;

class FindBootable
{
	/**
	 * @param string $bootableComponentName
	 *
	 * @return BootableComponentInterface|null
	 */
	public static function findBootable(string $bootableComponentName): ?BootableComponentInterface
	{
		$className = 'Boot'.Str::ucfirst($bootableComponentName);
		/** @var class-string<BootableComponentInterface> $namespace */
		$namespace = Str::rtrim(BootableComponentInterface::class, 'BootableComponentInterface').$className;
		// todo instance of BootableComponentInterface
		if (class_exists($namespace)) {
			return new $namespace();
		}

		return null;
	}
}
