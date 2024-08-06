<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser;

use Closure;
use Exception;
use Filefabrik\Bootraiser\Support\Str\Namespacering;
use Illuminate\Foundation\Events\DiscoverEvents;
use Illuminate\Support\Str;
use SplFileInfo;

/**
 * For *EventServiceProvider
 * todo manual events
 */
trait WithBootraiserEvent
{
	public static ?Closure $bootraiserEventDiscovery = null;

	// can has enabled trait
	public static array $packagesUseEvents = [];

	/**
	 * @throws Exception
	 */
	protected function registeringEvents(): void
	{
		self::$packagesUseEvents[] = BootraiserManager::getPackageConfig($this);

		// todo check class is instance of EventServiceProvider
		// initialize the package if not already done

		if (! self::$bootraiserEventDiscovery) {
			self::$bootraiserEventDiscovery = self::getBootraiserEventDiscovery();
			DiscoverEvents::guessClassNamesUsing(self::$bootraiserEventDiscovery);
		}
	}

	/**
	 * Determine if events and listeners should be automatically discovered.
	 *
	 * @return bool
	 */
	public function shouldDiscoverEvents(): bool
	{
		if (! $this->isPackageAutodiscover()) {
			return false;
		}

		return get_class($this) === static::class && (static::$shouldDiscoverEvents ?? false) === true;
	}

	/**
	 * Get the base path to be used during event discovery.
	 *
	 * @return string|null
	 * @throws Exception
	 */
	protected function eventDiscoveryBasePath(): ?string
	{
		// todo check is app(laravel base) or /src package
		return BootraiserManager::getPackageConfig($this)
								?->concatPackagePath('src')
		;
	}

	/**
	 * Get the listener directories that should be used to discover events.
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function discoverEventsWithin(): array
	{
		return [BootraiserManager::getPackageConfig($this)
								 ?->concatPackagePath('src/Listeners')];
	}

	public static function getBootraiserEventDiscovery(): Closure
	{
		// todo move out it is only a file-finder

		return function(SplFileInfo $file, $basePath) {
			// check we have a bootraiser controlled component
			$package = BootraiserManager::byPath($file->getRealPath());

			if ($package && self::isPackageAutodiscoverStatic($package)) {
				$class = $package->concatPackageNamespace(
					'Listeners',
					$file->getBasename('.php'),
				);
				if (class_exists($class)) {
					return $class;
				}
			}

			// original body from Laravel @see DiscoverEvents::classFromFile
			$class = trim(Str::replaceFirst($basePath, '', $file->getRealPath()), DIRECTORY_SEPARATOR);

			return str_replace(
				[DIRECTORY_SEPARATOR, ucfirst(basename(app()->path())).Namespacering::Divider],
				[Namespacering::Divider, app()->getNamespace()],
				ucfirst(Str::replaceLast('.php', '', $class)),
			);
		};
	}

	protected function isPackageAutodiscover(): bool
	{
		return in_array(BootraiserManager::getPackageConfig($this), self::$packagesUseEvents);
	}

	protected static function isPackageAutodiscoverStatic($package): bool
	{
		return in_array($package, self::$packagesUseEvents);
	}
}
