<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser;

use Exception;
use Filefabrik\Bootraiser\Support\PackageConfig;
use Filefabrik\Bootraiser\Support\Str\Namespacering;
use Illuminate\Foundation\Events\DiscoverEvents;
use Illuminate\Support\Str;
use SplFileInfo;

/**
 * For *EventServiceProvider
 */
trait WithBootraiserEvent
{
	use WithBootraiser;

	public static ?\Closure $bootraiserEventDiscovery = null;

	/**
	 * @throws Exception
	 */
	protected function registeringEvents(?PackageConfig $packageConfig = null): void
	{
		// todo check class is instance of EventServiceProvider
		// initialize the package if not already done
		$packageConfig ?? $this->bootraiserPackage();

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
		return get_class($this) === __CLASS__ && (static::$shouldDiscoverEvents ?? false) === true;
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
		return $this->bootraiserPackage()
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
		return [$this->eventDiscoveryBasePath().'/Listeners'];
	}

	public static function getBootraiserEventDiscovery(): \Closure
	{
        // todo move out it is only a file-finder
		return function(SplFileInfo $file, $basePath) {

			$cfg = BootraiserManager::searchPackage($file->getRealPath());
			if ($cfg) {
				$class = $cfg
					->concatPackageNamespace('Listeners',$file->getBasename('.php'))
				;
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
}
