<?php declare(strict_types=1);
/**
 * PHP version 8.2
 *
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Console\Commands\Database;

use Filefabrik\Bootraiser\BootraiserManager;
use Filefabrik\Bootraiser\Concerns\SuggestSeeder;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

/**
 *
 */
class SeedCommand extends \Illuminate\Database\Console\Seeds\SeedCommand
{

    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return 1;
        }

        $suggestSeeder = new SuggestSeeder($this);

        $menu = $suggestSeeder->mainMenu();

        if ($menu === 'list_all_seeder') {
            $pckgs = $suggestSeeder->listPackagesSeeders();
            dd($pckgs);
        }

        return;

        // input masks

        return parent::handle();
    }

    /**
     * @throws BindingResolutionException
     */
    protected function getSeeder(): \Illuminate\Database\Seeder|null
    {
        // todo check --all flag so iterate all found bootraiser and the default app-seeder
        // seeder in package
        // todo check we have --package to handle the DatabaseSeeder-Class from package if exists.

        return $this->handleOverrideSeeders() ? null : parent::getSeeder();
    }

    /**
     * the real magic, handles the packages they are under BootraiserManager registered
     *
     * @return bool
     * @throws BindingResolutionException
     */
    protected function handleOverrideSeeders(): bool
    {
        $hasAllOption = $this->hasOption('all');

        if ($hasAllOption) {
            $packages = BootraiserManager::packages();
            if (count($packages)) {
                foreach ($packages as $package) {
                    // 1 item with a bool flag, nothing else needed
                    if (count($package->getPool('DatabaseSeeder'))) {
                        $seederClass = $package->concatNamespace('Database\Seeders\DatabaseSeeder');

                        $this->seedClass($seederClass);
                    }
                }
            }

            if ($invocableSeeder = parent::getSeeder()) {
                // call th DatabaseSeeder from laravel /databases/seeders/DatabaseSeeder.php
                $this->invoker($invocableSeeder);
            }

            return true;
        }

        $hasPackageOption = $this->hasOption('package');

        if ($hasPackageOption) {
            $packageName = $this->option('package');
            $package     = BootraiserManager::searchPackage($packageName);

            // try sub-seeder
            $hasClassOption = $this->hasOption('class');
            if ($hasClassOption) {
                $className           = $this->option('class');
                $availableSubSeeders = $package->getPool('Seeders');
                // searching the wanted seeder class in files
                $foundClass = false;
                foreach ($availableSubSeeders as $namespace) {
                    if (Str::endsWith('\\' . $className, $namespace,)) {
                        $this->seedClass($namespace);
                        $foundClass = true;
                    }
                }

                if (!$foundClass) {
                    $this->error('Seeder ' . $className . ' not found in ' . $packageName);
                }

                // has executed, no parent call
                return true;
            }

            // only the package DatabaseSeeder
            if ($package && count($package->getPool('DatabaseSeeder'))) {
                $seederClass = $package->concatNamespace('Database\Seeders\DatabaseSeeder');
                if (class_exists($seederClass)) {
                    $this->seedClass($seederClass);
                }
                // else no class found, maybe miesconfigured bootraiser in a package config
            }

            return true;
        }

        // has handelt overrides, if not handle the parent command
        return false;
    }

    /**
     * Invoke
     *
     * @param $namespacedClass
     *
     * @return void
     * @throws BindingResolutionException
     */
    protected function seedClass($namespacedClass): void
    {
        $invocableSeeder = $this->laravel->make($namespacedClass)
                                         ->setContainer($this->laravel)
                                         ->setCommand($this)
        ;

        $this->invoker($invocableSeeder);
    }

    /**
     * @param $invocableSeeder
     *
     * @return void
     */
    protected function invoker($invocableSeeder)
    {
        // call th DatabaseSeeder from a Package
        Model::unguarded(fn() => $invocableSeeder->__invoke());
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this->getDefinition()
             ->addOption(
                 new InputOption(
                     '--package',
                     null,
                     InputOption::VALUE_REQUIRED,
                     'Seed command in a package which is using bootraiser',
                 ),

             )
        ;
        $this->getDefinition()
             ->addOption(new InputOption(
                             '--all',
                             'wp',
                             InputOption::VALUE_OPTIONAL,
                             'Seeds the Laravel Base application seeder and all found "DatabaseSeeder" they are tracked with bootraiser in packages',
                         ),)
        ;
    }
}
