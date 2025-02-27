<?php

declare(strict_types=1);

namespace Meius\FlagForge\Traits;

use JsonException;
use Meius\FlagForge\Contracts\Bitwiseable;
use UnexpectedValueException;

trait Serializable
{
    public function serialize(): string
    {
        return json_encode($this->__serialize(), JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public function unserialize(string $data): void
    {
        $decoded = json_decode(
            json: $data,
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );

        if (!is_array($decoded)) {
            throw new UnexpectedValueException('Invalid serialized data.');
        }

        $this->__unserialize($decoded);
    }

    public function jsonSerialize(): array
    {
        return $this->__serialize();
    }

    public function __serialize(): array
    {
        return [
            'enum' => $this->enum,
            'flags' => array_map(fn(Bitwiseable $case) => $case->value, $this->toArray()),
        ];
    }

    public function __unserialize(array $data): void
    {
        if (!isset($data['enum'], $data['flags']) || !is_string($data['enum']) || !is_array($data['flags'])) {
            throw new UnexpectedValueException('Invalid unserialized data.');
        }

        $this->enum = $data['enum'];

        if (!enum_exists($this->enum) || !is_subclass_of($this->enum, Bitwiseable::class)) {
            throw new UnexpectedValueException("Invalid enum class: $this->enum");
        }

        $activeCases = [];

        foreach ($data['flags'] as $value) {
            $activeCase = $this->enum::tryFrom($value);

            if ($activeCase === null) {
                throw new UnexpectedValueException("Invalid flag value $value for enum $this->enum");
            }

            $activeCases[] = $activeCase;
        }

        $this->mask = $this->combine(...$activeCases)->getMask();
    }
}
