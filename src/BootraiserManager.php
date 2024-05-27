<?php declare(strict_types=1);
/**
 * PHP version 8.2
 *
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser;

use Filefabrik\Bootraiser\Support\PackageConfig;
use Illuminate\Support\ServiceProvider;

class BootraiserManager
{
    protected static ?self $instance = null;

    /**
     * @var array<string,PackageConfig>
     */
    protected array $packageConfigs = [];

    public static function getPackageConfig(string                             $name,
                                            null|PackageConfig|ServiceProvider $config = null): PackageConfig
    {
        $instance = self::get();
        if ($instance->hasConfig($name)) {
            return $instance->getConfig($name);
        }
        if (!$config) {
            throw new \Exception('Configuration is empty. So can not create Bootraiser Package config');
        }

        return $instance->packageConfigs[$name] = PackageConfig::from($config);
    }

    public static function get()
    {
        return self::$instance ??= new self();
    }

    /**
     * @return array<string,PackageConfig>
     */
    public static function getPackages(): array
    {
        return self::get()->getPackageConfigs();
    }


    public static function searchPackage(string $name): ?PackageConfig
    {
        // more searchable stuff via iterating each pa
        $instance = self::get();
        if ($instance) {
            // search via index
            $found = $instance->packageConfigs[$name] ?? null;
            if ($found) {
                return $found;
            }
            foreach ($instance->packageConfigs as $packageConfig) {
                if (
                    in_array($name,
                             [$packageConfig->getGroupName(),
                              $packageConfig->getVendorPackageName(),
                              $packageConfig->groupOrVendorName()])
                ) {
                    return $packageConfig;
                }
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
