<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Support;

use Filefabrik\Bootraiser\Support\Str\Namespacering;
use Filefabrik\Bootraiser\Support\Str\Pathering;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use UnexpectedValueException;

/**
 * Each Vendor Package has his own basic config
 */
class PackageConfig
{
    use PackageConfigDataTrait;

    /**
     * @var string|null
     */
    private ?string $groupName = null;

    /**
     * @var string
     */
    private string $basePath;

    /**
     * @var string
     */
    private string $vendorPackageName;

    /**
     * @var string
     */
    private string $namespace;

    /**
     * @param string $basePath
     * @param string $vendorPackageName
     * @param string $namespace
     */
    public function __construct(string $basePath, string $vendorPackageName, string $namespace)
    {
        $this->setBasePath($basePath)
             ->setNamespace($namespace)
             ->setVendorPackageName($vendorPackageName)
        ;
    }

    /**
     * @param $config
     *
     * @return PackageConfig
     */
    public static function from($config): PackageConfig
    {
        if ($config instanceof PackageConfig) {
            return $config;
        }
        if ($config instanceof ServiceProvider) {
            return self::fromServiceProvider($config);
        }

        throw new UnexpectedValueException('Bootraiser not properly configured');
    }

    /**
     * @param ServiceProvider $serviceProvider
     *
     * @return PackageConfig
     */
    public static function fromServiceProvider(ServiceProvider $serviceProvider): PackageConfig
    {
        $cls              = new \ReflectionClass($serviceProvider);
        $isLaravel        = false;

        // todo testing!
        // todo location from where the service called from
        $srcPackageStarts = dirname(pathinfo($cls->getFileName(), PATHINFO_DIRNAME));

        if (str_ends_with($srcPackageStarts, '/src')) {
            // regular package
            $packageStart = Str::replaceEnd('/src', '', $srcPackageStarts);
        }
        elseif (str_ends_with($srcPackageStarts, '/app')) {
            // laravel main package
            $packageStart = Str::replaceEnd('/app', '', $srcPackageStarts);
            $isLaravel    = true;
        }
        else {
            throw new UnexpectedValueException('Package can not be auto-detected');
        }

        $relPackageDirectory = Str::replaceStart(base_path(), '', $packageStart);

        $packageName = Str::afterLast($relPackageDirectory, '/');

        $packageNamespace = Str::replace(
            '\\Providers',
            '',
            $cls->getNamespaceName(),
        );

        $package = new PackageConfig(
            $packageStart,
            $packageName,
            $packageNamespace,
        );

        if ($isLaravel) {
            $package->setVendorPackageName('Laravel');
        }

        return $package;
    }

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * @param string $basePath
     *
     * @return $this
     */
    public function setBasePath(string $basePath): static
    {
        $this->basePath = Pathering::withEnd(realpath($basePath));

        return $this;
    }

    /**
     * @return string
     */
    public function getVendorPackageName(): string
    {
        return $this->vendorPackageName;
    }

    /**
     * @param string $vendorPackageName
     *
     * @return $this
     */
    public function setVendorPackageName(string $vendorPackageName): static
    {
        $this->vendorPackageName = $this->modifyName($vendorPackageName);

        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     *
     * @return $this
     */
    public function setNamespace(string $namespace): static
    {
        $this->namespace = Namespacering::withEnd($namespace);

        return $this;
    }


    /**
     * @param string $name
     *
     * @return string
     */
    protected function modifyName(string $name): string
    {
        return Str::lower($name);
    }

    /**
     * @return string|null
     */
    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    /**
     * @return string
     */
    public function groupOrVendorName(): string
    {
        return $this->getGroupName() ?? $this->getVendorPackageName();
    }

    /**
     * @param string $groupName
     *
     * @return $this
     */
    public function setGroupName(string $groupName): PackageConfig
    {
        $this->groupName = $this->modifyName($groupName);

        return $this;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function concatPackagePath(... $path): string
    {
        return Pathering::concat($this->basePath, ...$path);
    }

    /**
     * @param string $namespace
     *
     * @return string
     */
    public function concatPackageNamespace(...$namespace): string
    {
        return Namespacering::concat($this->namespace, ...$namespace);
    }

    /**
     * @param string $groupName
     *
     * @return string
     */
    public function concatGroupName(string $groupName): string
    {
        return $this->groupOrVendorName() . '-' . $groupName;
    }
}
