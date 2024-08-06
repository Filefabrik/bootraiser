<?php declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Facades;

use Filefabrik\Bootraiser\Raisilence\Assets;
use Filefabrik\Bootraiser\Raisilence\Commands;
use Filefabrik\Bootraiser\Raisilence\Components;
use Filefabrik\Bootraiser\Raisilence\Configs;
use Filefabrik\Bootraiser\Raisilence\Livewire;
use Filefabrik\Bootraiser\Raisilence\Migrations;
use Filefabrik\Bootraiser\Raisilence\Routes;
use Filefabrik\Bootraiser\Raisilence\Translations;
use Filefabrik\Bootraiser\Raisilence\Views;

class Mapper
{
	/**
	 * @var array|null
	 */
	public static ?array $useMapper = null;

	/**
	 * @var array|string[]
	 */
	protected static array $defaultMapper = [
		'Migrations'   => Migrations::class,
		'Routes'       => Routes::class,
		'Configs'      => Configs::class,
		'Translations' => Translations::class,
		'Views'        => Views::class,
		'Components'   => Components::class,
		'Commands'     => Commands::class,
		'Assets'       => Assets::class,
		'Livewire'     => Livewire::class,
	];

	/**
	 * @return array|string[]
	 */
	public static function getMapper(): array
	{
		return self::$useMapper ??= self::getDefaultMapper();
	}

	/**
	 * @return array|string[]
	 */
	public static function getDefaultMapper(): array
	{
		return self::$defaultMapper;
	}
}
