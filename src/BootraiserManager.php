<?php declare(strict_types=1);
/**
 * PHP version 8.2
 *
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser;

use Filefabrik\Bootraiser\Support\PackageConfig;

class BootraiserManager
{
    protected static ?self $instance = null;
    /**
     * @var array<string,PackageConfig>
     */
    protected array $configs = [];

    public static function getPackageConfig(string $name, PackageConfig|array $config = []): PackageConfig
    {
        self::$instance ??= new self();

        if (self::$instance->hasConfig($name)) {
            return self::$instance->getConfig($name);
        }
        if (!$config) {
            throw new \Exception('Configuration is empty. So can not create Bootraiser Package config');
        }

        return self::$instance->configs[$name] = is_array($config) ? PackageConfig::fromArray(...$config) : $config;
    }

    protected function hasConfig(string $name): bool
    {
        return !!($this->configs[$name]??null);
    }

    public function getConfig(string $name): PackageConfig
    {
        return $this->configs[$name];
    }
}
