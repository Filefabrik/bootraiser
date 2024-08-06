<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Raisilence;

use Filefabrik\Bootraiser\Contracts\ContractInPath;
use Filefabrik\Bootraiser\Contracts\ContractOutPath;
use Filefabrik\Bootraiser\Contracts\ContractRaisilenceCore;
use Filefabrik\Bootraiser\Raisilence\Support\InPath;
use Filefabrik\Bootraiser\Raisilence\Support\OutPath;
use Filefabrik\Bootraiser\Raisilence\Support\TraitPublicServiceProvider;
use Filefabrik\Bootraiser\Support\Str\Pathering;

/**
 * Prepare routes to keep the boot trait thin
 * todo rollout routes into routes/vendor/packageName/
 *
 * @see https://laravel.com/docs/11.x/packages#routes
 */
class Routes implements ContractRaisilenceCore, ContractInPath, ContractOutPath
{
	use TraitPublicServiceProvider;
	use InPath;
	use OutPath;

	// find-path in package
	protected static ?string $in_path = 'routes';

	// relative to laravel
	protected static ?string $out_path = 'routes';

	/**
	 * load published and not-published routes
	 *
	 * @return static
	 */
	public function load(): static
	{
		// todo optional prefix for published route files if packages uses web.php or api.php because laravel uses in core already the api.php naming

		// todo make routes disableable if the dev. overrides full
		$routeTypes = $this->prepareLoad();
		// todo decide disable routes from inside the package if published
		$this->integrateRoutes($routeTypes['published']);
		// todo decide should unpublish routes from a package be load
		$this->integrateRoutes($routeTypes['not-published']);

		// load the override files

		return $this;
	}

	private function integrateRoutes(array $routePaths): void
	{
		foreach ($routePaths as $path) {
			$this->getPublicServiceProvider()
				 ->loadRoutesFrom($path)
			;
		}
	}

	/**
	 * make package routes publishable (and editable) for developers/users
	 *
	 *
	 * @return static
	 */
	public function publish(): static
	{
		if ($this->runningInConsole() && $prepared = $this->preparePublish()) {
			$this->getPublicServiceProvider()
				 ->publishes($prepared, $this->packageConfig->concatPackageName('routes'))
			;
		}

		return $this;
	}

	protected function preparePublish(): array
	{
		$prepared = [];

		foreach ($this->all() ?? [] as [$file, $basename]) {
			$prepared[$file] = $this->modifyTargetFile($file);
		}

		return $prepared;
	}

	/**
	 * Prepare the from => to key value pairs
	 */
	protected function all(): ?array
	{
		$finder = $this->inFiles();
		if ($finder && $finder->hasResults()) {
			$files = [];
			foreach ($finder as $file) {
				// todo move format file is key
				$files[] = [$file->getRealPath(), $file->getBasename()];
			}

			return $files;
		}

		return null;
	}

	/**
	 * File-Names can be manipulated for outputting
	 */
	protected function modifyTargetFile(string $file): string
	{
		$outDir = [self::$out_path];

		$outDir[] = pathinfo($file, PATHINFO_BASENAME);

		return base_path(Pathering::concat(...$outDir));
	}

	/**
	 * @return array{published: array<int,string>, not-published: array<int,string>}
	 */
	protected function prepareLoad(): array
	{
		// todo strategy
		// check approach from https://www.laravelpackage.com/09-routing/#configurable-route-prefix-and-middleware
		$routes           = ['published' => [], 'not-published' => []];
		$perhapsPublished = $this->preparePublish();
		//  todo boot routes by pattern they are in laravel routes/
		if ($perhapsPublished) {
			// first check already published to out
			foreach ($perhapsPublished as $originalFile => $file) {
				if (is_file($file)) {
					$routes['published'][] = $originalFile;

					unset($perhapsPublished[$originalFile]);
				}
			}
		}
		if ($perhapsPublished) {
			foreach ($this->all() ?? [] as [$originalFile, $key]) {
				if ($perhapsPublished[$originalFile] ?? null) {
					$routes['not-published'][] = $originalFile;
				}
			}
		}

		return $routes;
	}
}
