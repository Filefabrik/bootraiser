<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Support;

use Illuminate\Database\Eloquent\Collection;

/**
 * Configs for diver unknown configs if need
 */
trait PackageConfigDataTrait
{
	/**
	 * @var array<string,Collection>
	 */
	private array $configs = [];

	/**
	 * @return $this
	 */
	public function add(string $poolName, $item): static
	{
		$this->getConfig($poolName)
			->add($item)
		;

		return $this;
	}

	public function getConfig(string $poolName): Collection
	{
		return $this->configs[$poolName] ??= new Collection();
	}
}
