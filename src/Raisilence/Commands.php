<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Raisilence;

use Filefabrik\Bootraiser\Contracts\ContractInNamespace;
use Filefabrik\Bootraiser\Contracts\ContractInPath;
use Filefabrik\Bootraiser\Contracts\ContractRaisilenceCore;
use Filefabrik\Bootraiser\Raisilence\Support\InNamespace;
use Filefabrik\Bootraiser\Raisilence\Support\InPath;
use Filefabrik\Bootraiser\Raisilence\Support\TraitPublicServiceProvider;
use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command;
use ReflectionClass;

/**
 * @see https://laravel.com/docs/11.x/packages#commands
 */
class Commands implements ContractRaisilenceCore, ContractInPath, ContractInNamespace
{
	use TraitPublicServiceProvider;
	use InPath;
	use InNamespace;

	protected static ?string $in_path = 'src/Console/Commands';

	protected static ?string $in_namespace = 'Console\Commands';

	public function load(): static
	{
		// does not boot /app commands they are booted by laravel core

		if ($this->runningInConsole() && $finder = $this->inFiles()) {
			foreach ($finder->getIterator() as $file) {
				$cls     = $file->getBasename('.php');
				$command = $this->packageConfig->concatPackageNamespace(self::$in_namespace, $cls);

				if (is_subclass_of($command, Command::class) && ! (new ReflectionClass($command))->isAbstract()) {
					Artisan::starting(fn(Artisan $artisan) => $artisan->resolve($command));
				}
			}
		}

		return $this;
	}

	public function publish(): static
	{
		// nothing to do
		return $this;
	}
}
