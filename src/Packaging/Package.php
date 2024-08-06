<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Packaging;

use Filefabrik\Bootraiser\Contracts\ContractPackageConfig;
use Filefabrik\Bootraiser\Support\PackageConfigDataTrait;
use Filefabrik\Bootraiser\Support\Str\Namespacering;
use Filefabrik\Bootraiser\Support\Str\Pathering;
use Illuminate\Support\Str;

/**
 * Each Vendor Package has his own basic config
 */
class Package implements ContractPackageConfig
{
	use PackageConfigDataTrait;

	/**
	 * @var string
	 */
	private string $basePath;

	/**
	 * @var string
	 */
	private string $packageName;

	/**
	 * @var string
	 */
	private string $namespace;

	/**
	 * @param string $basePath
	 * @param string $packageName
	 * @param string $namespace
	 */
	public function __construct(string $basePath, string $packageName, string $namespace)
	{
		$this->setBasePath($basePath)
			 ->setNamespace($namespace)
			 ->setPackageName($packageName)
		;
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
	public function getPackageName(): string
	{
		return $this->packageName;
	}

	/**
	 * After vendor/
	 *
	 * @param string $packageName
	 *
	 * @return $this
	 */
	public function setPackageName(string $packageName): static
	{
		$this->packageName = $this->modifyName($packageName);

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
	 * @param ...$path
	 *
	 * @return string
	 */
	public function concatPackagePath(...$path): string
	{
		return Pathering::concat($this->basePath, ...$path);
	}

	/**
	 * @param ...$namespace
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
	public function concatPackageName(string $groupName): string
	{
		return $this->getPackageName().'-'.$groupName;
	}
}
