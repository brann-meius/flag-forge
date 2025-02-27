<?php

declare(strict_types=1);

namespace Meius\FlagForge\Traits;

use Meius\FlagForge\Contracts\Bitwiseable;
use Traversable;

trait Walkable
{
    public function getIterator(): Traversable
    {
        yield from $this->toArray();
    }

    public function toArray(): array
    {
        return array_filter($this->enum::cases(), fn (Bitwiseable $case): bool => $this->has($case));
    }
}
