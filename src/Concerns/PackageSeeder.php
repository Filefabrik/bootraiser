<?php declare(strict_types=1);
/**
 * PHP version 8.2
 *
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Concerns;

use Filefabrik\Bootraiser\Support\PackageConfig;
use Illuminate\Support\Collection;

/**
 *
 */
class PackageSeeder
{
    /**
     * @var string
     */
    protected string $relDir = 'database/seeders';

    /**
     * @param PackageConfig $config
     */
    public function __construct(readonly protected PackageConfig $config)
    {
    }

    /**
     *
     * @return array<string,string|Collection|null>|null
     */
    public function findSeeder(): ?array
    {
        $seeders = [];

        $databaseSeeder = $this->databaseSeeder();

        // DatabaseSeeder is relevant for the whole Package
        $seeders['DatabaseSeeder'] = $databaseSeeder ?: null;

        $databaseSubSeeder = $this->subSeeders();

        $seeders['Seeders'] = $databaseSubSeeder ?: null;

        return $seeders ?: null;
    }

    /**
     * @return string|null
     */
    public function databaseSeeder(): ?string
    {
        return SeederFiles::databaseSeeder($this->getPath());
    }

    /**
     * @return array|null
     */
    public function subSeeders(): ?array
    {
        $path = $this->getPath();
        if ($path) {
            $seeders           = [];
            $databaseSubSeeder = SeederFiles::databaseSubSeeders($path);
            // Other Classes in package/database/seeder/ used for particular execution or from the DatabaseSeeder.php
            foreach ($databaseSubSeeder?->getIterator() ?? [] as $file) {
                // todo check is seeder class
                $cls       = $file->getBasename('.php');
                $namespace = $this->config->concatNamespace('Database\\Seeders\\' . $cls);
                // all other seeders
                $seeders[] = $namespace;
            }

            return $seeders;
        }

        return null;
    }

    /**
     * @return string|null
     */
    protected function getPath(): ?string
    {
        $seedersDir = $this->config->concatPath($this->relDir);

        return is_dir($seedersDir) ? $seedersDir : null;
    }

}
