<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser;

use Exception;
use Filefabrik\Bootraiser\Packaging\FromServiceProvider;
use Filefabrik\Bootraiser\Packaging\Package;
use Filefabrik\Bootraiser\Support\Str\Namespacering;
use Filefabrik\Bootraiser\Support\Str\Pathering;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

/**
 * Stacking all bootraiser configs
 */
class BootraiserManager
{
	/**
	 * @var array<string,Package>
	 */
	protected array $packages = [];

	// different ways to hook packages. VendorPackages holds the re
	/**
	 * Linear to find fast already registered Packages
	 *
	 * @var array
	 */
	protected array $multiIndexer = [];

	/**
	 * todo make something other which is configurable
	 */
	public static function getPackageConfig(null|string|Package|ServiceProvider $config = null): Package
	{
		// todo make package config based upon the real namespace or/and for the real package
		// todo otherwise multiple ServiceProviders create there own Namespace

		// starts with namespace is a ClassName
		// without backslash and 1 slash (vendor/package) it is the correct name
		$instance = self::get();

		if ($config instanceof Package) {
			return $instance->byPackage($config);
		}

		if ($config instanceof ServiceProvider) {
			return $instance->byServiceProvider($config);
		}

		// by name
		$package = $instance->locateByString($config);
		if ($package) {
			return $package;
		}

		throw new Exception('Package can not be found');
	}

	public static function byIdentifier(string $identifier): ?Package
	{
		return self::get()
				   ->locateByString($identifier)
		;
	}

	public static function byPath(string $path): ?Package
	{
		return self::get()
				   ->viaDirectory($path)
		;
	}

	protected function locateByString(string $identifier): ?Package
	{
		$package = $this->getRegisteredPackage($identifier);
		if ($package) {
			return $package;
		}
		$package = $this->viaDirectory($identifier);
		if ($package) {
			return $package;
		}

		return null;
	}

	public function getRegisteredPackage(string $identifier): ?Package
	{
		return ($index = $this->isRegisteredPackage($identifier)) ? $this->packages[$index] : null;
	}

	public function isRegisteredPackage(string $identifier): ?string
	{
		return $this->multiIndexer[$identifier] ?? null;
	}

	public function byServiceProvider(ServiceProvider $serviceProvider): Package
	{
		$namespaceName = self::packageNameFromServiceProvider($serviceProvider::class);
		$package       = $this->getRegisteredPackage($namespaceName);

		if (! $package) {
			// else register the Package
			$package = FromServiceProvider::fromServiceProvider($serviceProvider);
			$this->indexPackage($package);
		}

		return $package;
	}

	public function byPackage(Package $packageConfig): ?Package
	{
		$package = $this->getRegisteredPackage($packageConfig->getPackageName());

		// not indexed so auto-index
		$package ?: $this->indexPackage($package);

		return $package;
	}

	public function viaDirectory(string $identifier): ?Package
	{
		$package = $this->directoryByEnding($identifier, '/src');
		if (! $package) {
			$package = $this->directoryByEnding($identifier, '/app');
		}

		return $package;
	}

	protected function directoryByEnding($identifier, $relDir): ?Package
	{
		$packageBasePath = Str::beforeLast($identifier, $relDir).Pathering::Divider;
		if ($index = $this->isRegisteredPackage($packageBasePath)) {
			return $this->getRegisteredPackage($index);
		}

		return null;
	}

	// indexing package with different ways to speed up searching/finding packages
	protected function indexPackage(Package $packageConfig): void
	{
		$packageName                  = $packageConfig->getPackageName();
		$this->packages[$packageName] = $packageConfig;
		$this->multiIndexer           = array_merge(
			$this->multiIndexer,
			[$packageName                   => $packageName,
				$packageConfig->getNamespace() => $packageName,
				// package base_path
				$packageConfig->getBasePath() => $packageName,
			],
		);
	}

	/**
	 * Keep Package Config instance
	 *
	 * @return BootraiserManager
	 */
	public static function get(): BootraiserManager
	{
		if (! app()->has(self::class)) {
			app()->singleton(self::class, fn() => new self());
		}

		return app(self::class);
	}

	/**
	 * Todo test
	 *
	 * @return array<string,Package>|null
	 */
	public static function packages(): ?array
	{
		return self::get()->packages;
	}

	public static function packageNameFromServiceProvider(string $name): ?string
	{
		$beforeLast = Str::beforeLast($name, Namespacering::Divider.'Providers');

		return $beforeLast.Namespacering::Divider;
	}
}
