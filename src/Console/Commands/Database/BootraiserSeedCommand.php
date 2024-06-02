<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Console\Commands\Database;

class BootraiserSeedCommand extends \Illuminate\Database\Console\Seeds\SeedCommand
{
	use TraitSeedCommand;

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'bootraiser:seed';
}
