<?php

declare(strict_types=1);

namespace Meius\FlagForge\Contracts;

/**
 * Provides a contract for objects that can be converted to an array of Bitwiseable items.
 */
interface Arrayable
{
    /**
     * Converts the object into an array of Bitwiseable items.
     */
    public function toArray(): array;
}
