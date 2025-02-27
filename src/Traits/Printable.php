<?php

declare(strict_types=1);

namespace Meius\FlagForge\Traits;

use Meius\FlagForge\Contracts\Bitwiseable;
use Traversable;

trait Printable
{
    public function __toString(): string
    {
        return (string)$this->mask;
    }
}
