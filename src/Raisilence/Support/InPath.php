<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Raisilence\Support;

// todo make per package configurable
use Symfony\Component\Finder\Finder;

trait InPath
{
	/**
	 * @return string|null
	 */
	public function existingInPath(): ?string
	{
		$dir = $this->packageConfig->concatPackagePath(self::$in_path);

		return is_dir($dir) ? $dir : null;
	}

	public function inFiles(): ?Finder
	{
		$dir = $this->existingInPath();

		return $dir ?
			Finder::create()
				  ->name('*.php')
				  ->in($dir)
				  ->files()
			: null;
	}
}
