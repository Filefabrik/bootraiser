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
 * Loading translations from package/lang
 *
 * @see https://laravel.com/docs/11.x/packages#language-files
 */
class Translations implements ContractRaisilenceCore, ContractInPath, ContractOutPath
{
	use TraitPublicServiceProvider;
	use InPath;
	use OutPath;

	/**
	 * @var string|null
	 */
	protected static ?string $in_path = 'lang';

	/**
	 * relative under lang/
	 *
	 * @var string|null
	 */
	protected static ?string $out_path = 'vendor';

	public function load(): static
	{
		$dir = $this->existingInPath();

		if ($dir) {
			// todo has|use json-files or php-files, wenn kein json, lassen sich die json files sparen.
			// wenn keine lang dirs im original package sind, dann sind keine translations via file
			// falls eine json vorhanden ist, wird per laravel die json verwendet und nicht die php
			// todo check both is need or configurable
			$this->phpFromPackage($dir);

			// first JSON from package
			$this->jsonFromPackage($dir);
			// looking for json-files in /lang/vendor/package-name/*.json as published override
			$this->jsonFromOverride();
		}

		return $this;
	}

	protected function phpFromPackage(string $packageLangDir): void
	{
		$this->getPublicServiceProvider()
			 ->loadTranslationsFrom($packageLangDir, $this->packageConfig->getPackageName())
		;
	}

	protected function jsonFromPackage(string $packageLangDir): void
	{
		$this->getPublicServiceProvider()
			 ->loadJsonTranslationsFrom($packageLangDir)
		;
	}

	protected function jsonFromOverride(): void
	{
		// todo configure
		$outDir = lang_path('vendor/'.$this->packageConfig->getPackageName());
		if (is_dir($outDir)) {
			$this->getPublicServiceProvider()
				 ->loadJsonTranslationsFrom($outDir)
			;
		}
	}

	/**
	 * @return $this
	 */
	public function publish(): static
	{
		if ($this->runningInConsole() && $dir = $this->existingInPath()) {
			$this->getPublicServiceProvider()
				 ->publishes(
				 	[
				 		$dir => lang_path(Pathering::concat(
				 			self::$out_path,
				 			$this->packageConfig->getPackageName(),
				 		)),
				 	],
				 	$this->packageConfig->concatPackageName('translations'),
				 )
			;
		}

		// else log ---v no translations for package

		return $this;
	}
}
