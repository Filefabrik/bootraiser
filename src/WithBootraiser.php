<?php declare(strict_types=1);
/**
 * PHP version 8.2
 *
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser;

use Filefabrik\Bootraiser\Support\PackageConfig;
use Illuminate\Support\Arr;

/**
 * fill in each service provider
 */
trait WithBootraiser
{
    use Bootraiser;

    protected ?PackageConfig $packageConfig = null;

    protected function bootraiserRegister(...$parts): void
    {
        $flat = Arr::flatten([...$parts]);
        $this->registerBootraiserServices($this->bootraiserConfig(), $flat);
    }

    protected function bootraiserBoot(...$parts): void
    {
        $flat = Arr::flatten([...$parts]);
        $this->bootBootraiserServices($this->bootraiserConfig(), $flat);
    }

    protected function bootraiserConfig($config = null): PackageConfig
    {
        if ($this->packageConfig && !$config) {
            return $this->packageConfig;
        }

        return $this->packageConfig = BootraiserManager::getPackageConfig(static::class, $config ?? $this);
    }

}
