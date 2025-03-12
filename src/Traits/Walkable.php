<?php

declare(strict_types=1);

namespace Meius\FlagForge\Traits;

use Meius\FlagForge\Contracts\Bitwiseable;
use Meius\FlagForge\FlagManager;
use Traversable;

/**
 * Provides iteration and array conversion capabilities.
 *
 * Implements `getIterator()` to enable object traversal, yielding elements from `toArray()`.
 * The `toArray()` method returns an array of enum cases for which the corresponding flags are set.
 *
 * @mixin FlagManager
 */
trait Walkable
{
    public function getIterator(): Traversable
    {
        yield from $this->toArray();
    }

    public function toArray(): array
    {
        return array_filter($this->enum::cases(), fn(Bitwiseable $case): bool => $this->has($case));
    }
}
