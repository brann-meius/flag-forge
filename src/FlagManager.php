<?php

declare(strict_types=1);

namespace Meius\FlagForge;

use Closure;
use IteratorAggregate;
use JsonSerializable;
use Meius\FlagForge\Contracts\Arrayable;
use Meius\FlagForge\Contracts\Bitwiseable;
use Meius\FlagForge\Contracts\ManagerContract;
use Meius\FlagForge\Traits\Comparable;
use Meius\FlagForge\Traits\Printable;
use Meius\FlagForge\Traits\Serializable as SerializableTrait;
use Meius\FlagForge\Traits\Walkable;
use Serializable;
use Stringable;

class FlagManager implements ManagerContract, Serializable, JsonSerializable, IteratorAggregate, Arrayable, Stringable
{
    use Comparable;
    use SerializableTrait;
    use Walkable;
    use Printable;

    /**
     * @var class-string<Bitwiseable> $enum
     */
    protected string $enum;

    public function __construct(
        protected int $mask = 0
    ) {
        //
    }

    public function add(Bitwiseable $flag): self
    {
        return $this->executeOperationWithValidation(function () use ($flag): void {
            $this->mask |= $flag->value;
        }, $flag);
    }

    public function remove(Bitwiseable $flag): self
    {
        return $this->executeOperationWithValidation(function () use ($flag): void {
            $this->mask &= ~$flag->value;
        }, $flag);
    }

    public function combine(Bitwiseable ...$flags): self
    {
        return $this->executeOperationWithValidation(function () use ($flags): void {
            $this->mask = array_reduce(
                $flags,
                fn(int $carry, Bitwiseable $flag): int => $carry | $flag->value,
                $this->getMask()
            );
        }, ...$flags);
    }

    public function toggle(Bitwiseable ...$flags): self
    {
        return $this->executeOperationWithValidation(function () use ($flags): void {
            foreach ($flags as $flag) {
                $this->mask ^= $flag->value;
            }
        }, ...$flags);
    }

    public function clear(): self
    {
        $this->mask = 0;

        return $this;
    }

    public function getMask(): int
    {
        return $this->mask;
    }

    private function isValid(Bitwiseable $flag): bool
    {
        return $flag instanceof $this->enum;
    }

    private function initializeEnumClassFromFlags(Bitwiseable ...$flags): void
    {
        if (!isset($this->enum)) {
            foreach ($flags as $flag) {
                $this->enum = $flag::class;
            }
        }
    }

    /**
     * Executes a given operation on the flag manager, after initializing the enum class and validating flags.
     */
    private function executeOperationWithValidation(Closure $closure, Bitwiseable ...$flags): self
    {
        $this->initializeEnumClassFromFlags(...$flags);

        foreach ($flags as $flag) {
            if (!$this->isValid($flag)) {
                throw new \InvalidArgumentException('The provided flag is not part of the current enum.');
            }
        }

        $closure();

        return $this;
    }
}
