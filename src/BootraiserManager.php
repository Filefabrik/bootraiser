<?php declare(strict_types=1);
/**
 * PHP version 8.2
 *
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser;

use Exception;
use Filefabrik\Bootraiser\Support\PackageConfig;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class BootraiserManager
{
    protected static ?self $instance = null;

    /**
     * @var array<string,PackageConfig>
     */
    protected array $packageConfigs = [];

    /**
     * @throws Exception
     */
    public static function getPackageConfig(string                             $name,
                                            null|PackageConfig|ServiceProvider $config = null): PackageConfig
    {
        // todo make package config based upon the real namespace or/and for the real package
        // todo otherwise multiple ServiceProviders create there own Namespace
        //
        $instance = self::get();
        if ($instance->hasConfig($name)) {
            return $instance->getConfig($name);
        }
        if (!$config) {
            throw new \Exception('Configuration is empty. So can not create Bootraiser Package config');
        }

        return $instance->packageConfigs[$name] = PackageConfig::from($config);
    }

    public static function get(): BootraiserManager
    {
        return self::$instance ??= new self();
    }

    /**
     * @return array<string,PackageConfig>
     */
    public static function getPackages(): array
    {
        return self::get()
                   ->getPackageConfigs()
        ;
    }

    public static function searchPackage(string $name): ?PackageConfig
    {
        // more searchable stuff via iterating each pa
        $instance = self::get();

        // search via index
        $found = $instance->packageConfigs[$name] ?? null;
        if ($found) {
            return $found;
        }

        foreach ($instance->packageConfigs as $packageConfig) {
            $haystack = [$packageConfig->getGroupName(),
                         $packageConfig->getVendorPackageName(),
                         $packageConfig->groupOrVendorName()];

            if (in_array($name, $haystack)) {
                return $packageConfig;
            }

            // try to find if name is a full path.
            $packageBasePath = Str::beforeLast($name, '/src') . '/';
            // simple compare locations
            if ($packageBasePath === $packageConfig->getBasePath()) {
                return $packageConfig;
            }
        }

        return null;
    }

    /**
     * @return array<string,PackageConfig>|null
     */
    public static function packages(): ?array
    {
        return self::$instance?->packageConfigs;
    }

    protected function hasConfig(string $name): bool
    {
        return !!($this->packageConfigs[$name] ?? null);
    }

    public function getConfig(string $name): PackageConfig
    {
        return $this->packageConfigs[$name];
    }

    public function getPackageConfigs(): array
    {
        return $this->packageConfigs;
    }
}
