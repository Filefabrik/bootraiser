<?php

declare(strict_types=1);
/**
 * PHP version 8.2
 */
/** @copyright-header * */

namespace Filefabrik\Bootraiser\Contracts;

interface ContractRaisilenceBoot
{
	public function boot(): static;
}
