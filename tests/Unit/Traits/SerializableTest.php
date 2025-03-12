<?php

declare(strict_types=1);

namespace Meius\FlagForge\Tests\Unit\Traits;

use Exception;
use JsonException;
use Meius\FlagForge\FlagManager;
use Meius\FlagForge\Tests\Support\GroupType;
use Meius\FlagForge\Tests\Support\Permission;
use Meius\FlagForge\Tests\Support\ChocolateBarEnum;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * This test suite covers the basic functionalities of the FlagManager class:
 *
 * - Serialization and deserialization via JSON
 */
class SerializableTest extends TestCase
{
    /**
     * @throws Exception
     */
    #[Test]
    public function serializationBehavior(): array
    {
        $manager = (new FlagManager())
            ->add(Permission::SendMessages)
            ->add(Permission::AddUsers);

        $serialized = $manager->serialize();
        $this->assertIsString($serialized);

        return [
            'manager' => $manager,
            'serialized' => $serialized,
        ];
    }

    /**
     * @throws JsonException
     */
    #[Test, Depends('serializationBehavior')]
    public function deserializationBehavior(array $stack): void
    {
        $unserializedManager = new FlagManager();
        $unserializedManager->unserialize($stack['serialized']);

        $this->assertInstanceOf(FlagManager::class, $unserializedManager);
        $this->assertSame($stack['manager']->getMask(), $unserializedManager->getMask());
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function unserializeExcludingEnum(): void
    {
        $manager = new FlagManager();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid unserialized data.');

        $manager->unserialize(json_encode([
            'flags' => [
                Permission::SendMessages,
                Permission::AddUsers,
            ]
        ]));
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function unserializeExcludingFlags(): void
    {
        $manager = new FlagManager();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid unserialized data.');
        $manager->unserialize(json_encode([
            'enum' => Permission::class,
        ]));
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function unserializeNonBitwiseEnumClass(): void
    {
        $manager = new FlagManager();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage("Invalid enum class: " . GroupType::class);
        $manager->unserialize(json_encode([
            'enum' => GroupType::class,
            'flags' => [GroupType::Private, GroupType::Group]
        ]));
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function unserializeNonEnumClass(): void
    {
        $manager = new FlagManager();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage("Invalid enum class: " . ChocolateBarEnum::class);
        $manager->unserialize(json_encode([
            'enum' => ChocolateBarEnum::class,
            'flags' => [ChocolateBarEnum::BOUNTY, ChocolateBarEnum::TWIX]
        ]));
    }

    /**
     * @throws JsonException
     * @throws Exception
     */
    #[Test]
    public function unserializeWithBrokenMask(): void
    {
        $manager = new FlagManager();
        $manager->add(Permission::SendMessages)
            ->add(Permission::AddUsers);

        $serialized = $manager->serialize();
        $serializedArray = json_decode($serialized, true);
        $serializedArray['flags'][] = 1 << 32;

        $broken = json_encode($serializedArray);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid flag value ' . 1 << 32 . ' for enum ' . Permission::class);

        $manager->unserialize($broken);
    }

    #[Test]
    public function jsonSerializeBehavior(): void
    {
        $manager = new FlagManager();
        $manager->add(Permission::SendMessages)
            ->add(Permission::AddUsers);

        $this->assertSame(json_encode($manager->jsonSerialize()), json_encode($manager));
        $this->assertSame($manager->jsonSerialize()['enum'], Permission::class);
        $this->assertSame($manager->jsonSerialize()['flags'], [
            Permission::SendMessages->value,
            2 => Permission::AddUsers->value
        ]);
    }
}
