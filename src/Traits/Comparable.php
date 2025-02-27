<?php

declare(strict_types=1);

namespace Meius\FlagForge\Traits;

use Meius\FlagForge\Contracts\Bitwiseable;

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
