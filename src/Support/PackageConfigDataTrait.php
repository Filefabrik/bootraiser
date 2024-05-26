<?php declare(strict_types=1);
/**
 * PHP version 8.2
 *
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Support;

use Illuminate\Database\Eloquent\Collection;

trait PackageConfigDataTrait
{
    /**
     * @var array<string,Collection>
     */
    private array $dataPools = [];

    public function addPool(string $poolName, $datas = new Collection())
    {
        $this->dataPools[$poolName] ??= new Collection();
        $this->dataPools[$poolName]->merge($datas);

        return $this;
    }

    public function ontoPool(string $poolName, $item)
    {
        $this->dataPools[$poolName] ??= new Collection();
        $this->dataPools[$poolName]->add($item);

        return $this;
    }

    public function getPool(string $poolName)
    {
        return $this->dataPools[$poolName] ?? new Collection();
    }
}
