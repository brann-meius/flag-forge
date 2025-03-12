<?php

declare(strict_types=1);

namespace Meius\FlagForge\Traits;

use Meius\FlagForge\Contracts\Bitwiseable;
use Meius\FlagForge\FlagManager;

/**
 * Provides default methods for comparing bitwise flags.
 *
 * The `has` method checks if a given flag is set in the object's mask,
 * while `doesntHave` returns the inverse result.
 *
 * @mixin FlagManager
 */
trait Comparable
{
    public function has(Bitwiseable $flag): bool
    {
        if ($this->isValid($flag)) {
            return ($this->mask & $flag->value) === $flag->value;
        }

        return false;
    }

    public function doesntHave(Bitwiseable $flag): bool
    {
        return !$this->has($flag);
    }
}
