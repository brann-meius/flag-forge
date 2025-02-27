<?php

declare(strict_types=1);

namespace Meius\FlagForge\Contracts;

/**
 * A manager for working with bitwise enums.
 */
interface ManagerContract
{
    /**
     * Adds a flag to the current mask.
     */
    public function add(Bitwiseable $flag): self;

    /**
     * Removes a flag from the current mask.
     */
    public function remove(Bitwiseable $flag): self;

    /**
     * Toggles specified flags.
     */
    public function toggle(Bitwiseable ...$flag): self;

    /**
     * Combines multiple flags into the current mask.
     */
    public function combine(Bitwiseable ...$flags): self;

    /**
     * Clears all flags in the current mask.
     */
    public function clear(): self;

    /**
     * Checks if the specified flag is present in the current mask.
     */
    public function has(Bitwiseable $flag): bool;

    /**
     * Checks if the specified flag is not present in the current mask.
     */
    public function doesntHave(Bitwiseable $flag): bool;

    /**
     * Returns the current mask value.
     */
    public function getMask(): int;
}
