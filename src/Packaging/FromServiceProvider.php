<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Packaging;

use Filefabrik\Bootraiser\Support\Str\Pathering;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use ReflectionClass;
use UnexpectedValueException;

class FromServiceProvider
{
	/**
	 * @param ServiceProvider $serviceProvider
	 *
	 * @return Package
	 */
	public static function fromServiceProvider(ServiceProvider $serviceProvider): Package
	{
		$cls       = new ReflectionClass($serviceProvider);
		$isLaravel = false;

		// todo check that bootraiser raises for a whole package or are services in the package particular configured
		// todo testing!
		// todo location from where the service called from
		$srcPackageStarts = dirname(pathinfo($cls->getFileName(), PATHINFO_DIRNAME));

		if (str_ends_with($srcPackageStarts, '/src')) {
			// regular package
			$packageStart = Str::replaceEnd('/src', '', $srcPackageStarts);
		} elseif (str_ends_with($srcPackageStarts, '/app')) {
			// laravel main package
			$packageStart = Str::replaceEnd('/app', '', $srcPackageStarts);
			$isLaravel    = true;
		} else {
			throw new UnexpectedValueException(sprintf('Bootraiser: The Package "%s" can not be auto-detected', $serviceProvider::class));
		}
		// todo has to be vendor package name
		$relPackageDirectory = Str::replaceStart(base_path(), '', $packageStart);

		$packageName = Str::afterLast($relPackageDirectory, Pathering::Divider);

		$clsNs            = $cls->getNamespaceName();
		$packageNamespace = Str::beforeLast(
			$clsNs,
			'\Providers',
		);

		$package = new Package(
			$packageStart,
			$packageName,
			$packageNamespace,
		);

		if ($isLaravel) {
			$package->setPackageName('Laravel');
		}

		return $package;
	}
}
