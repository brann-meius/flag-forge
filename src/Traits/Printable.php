<?php

declare(strict_types=1);

namespace Meius\FlagForge\Traits;

use Meius\FlagForge\FlagManager;

/**
 * Provides a default implementation for converting an object to a string.
 *
 * When a class uses this trait, it automatically implements the __toString()
 * method, which returns the string representation of its 'mask' property.
 *
 * @mixin FlagManager
 */
trait Printable
{
    public function __toString(): string
    {
        return (string)$this->mask;
    }
}
