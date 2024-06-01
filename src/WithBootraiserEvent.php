<?php declare(strict_types=1);
/**
 * PHP version 8.2
 *
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser;

use Filefabrik\Bootraiser\Support\PackageConfig;
use Illuminate\Foundation\Events\DiscoverEvents;
use Illuminate\Support\Str;
use SplFileInfo;

trait WithBootraiserEvent
{
    use WithBootraiser;

    static public ?\Closure $bootraiserEventDiscovery = null;

    protected function registeringEvents(?PackageConfig $packageConfig = null): void
    {
        // todo check class is instance of EventServiceProvider
        // initialize the package if not already done
        $packageConfig ??= $this->bootraiserPackage();

        if (!self::$bootraiserEventDiscovery) {
            self::$bootraiserEventDiscovery = self::getBootraiserEventDiscovery();
            DiscoverEvents::guessClassNamesUsing(self::$bootraiserEventDiscovery);
        }
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return get_class($this) === __CLASS__ && (static::$shouldDiscoverEvents ?? false) === true;
    }

    /**
     * Get the base path to be used during event discovery.
     *
     * @return string|null
     */
    protected function eventDiscoveryBasePath(): ?string
    {
        return $this->bootraiserPackage()
                    ?->concatPath('src')
        ;
    }

    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array
     */
    protected function discoverEventsWithin()
    {
        return [$this->eventDiscoveryBasePath() . '/Listeners',];
    }

    public static function getBootraiserEventDiscovery(): \Closure
    {
        return function(SplFileInfo $file, $basePath) {
            $ck  = get_called_class();
            $cfg = BootraiserManager::searchPackage($file->getRealPath());
            if ($cfg) {
                $class = $cfg
                    ->concatNamespace('Listeners\\' . $file->getBasename('.php'))
                ;
                if (class_exists($class)) {
                    return $class;
                }
            }

            // original body from Laravel @see DiscoverEvents::classFromFile
            $class = trim(Str::replaceFirst($basePath, '', $file->getRealPath()), DIRECTORY_SEPARATOR);

            return str_replace(
                [DIRECTORY_SEPARATOR, ucfirst(basename(app()->path())) . '\\'],
                ['\\', app()->getNamespace()],
                ucfirst(Str::replaceLast('.php', '', $class)),
            );
        };
    }
}
