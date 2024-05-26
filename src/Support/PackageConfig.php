<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Support;

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
    public static function fromServiceProvider(ServiceProvider $serviceProvider)
    {
        $cls = new \ReflectionClass($serviceProvider);

        $srcPackageStarts = dirname(pathinfo($cls->getFileName(), PATHINFO_DIRNAME));

        if (str_ends_with($srcPackageStarts, '/src')) {
            // regular package
            $packageStart = Str::replaceEnd('/src', '', $srcPackageStarts);
        }
        elseif (str_ends_with($srcPackageStarts, '/app'))
        {
            // laravel main package
            $packageStart = Str::replaceEnd('/app', '', $srcPackageStarts);
        }
        else{
            throw new UnexpectedValueException('Package can not be auto-detected');
        }

        $relPackageDirectory = Str::replaceStart(base_path(), '', $packageStart);

        $packageName = Str::afterLast($relPackageDirectory, '/');

        $packageNamespace = Str::replace('\\Providers',
                                         '',
                                         $cls->getNamespaceName());

        return new PackageConfig($packageStart,
                                 $packageName,
                                 $packageNamespace);
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
        $this->basePath = realpath(Str::rtrim($basePath, '/')) . '/';

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
        $this->namespace = $this->trimNamespace($namespace,) . '\\';

        return $this;
    }

    /**
     * @param string $namespace
     *
     * @return string
     */
    protected function trimNamespace(string $namespace): string
    {
        return Str::trim($namespace, '\\');
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
    public function groupOrVendorName()
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
    public function concatPath(string $path): string
    {
        return $this->basePath . Str::ltrim($path, '/');
    }

    /**
     * @param string $namespace
     *
     * @return string
     */
    public function concatNamespace(string $namespace): string
    {
        return $this->namespace . $this->trimNamespace($namespace);
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
