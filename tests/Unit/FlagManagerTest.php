<?php

declare(strict_types=1);

namespace Meius\LaravelFilter\Tests\Unit;

use Exception;
use InvalidArgumentException;
use JsonException;
use Meius\LaravelFilter\Tests\Support\GroupType;
use Meius\LaravelFilter\Tests\Support\Permission;
use Meius\LaravelFilter\Tests\Support\PrehistoricEnum;
use Meius\LaravelFilter\Tests\Support\XPermission;
use PHPUnit\Framework\TestCase;
use Meius\FlagForge\FlagManager;
use UnexpectedValueException;

/**
 * This test suite covers the basic functionalities of the FlagManager class:
 * - Initial mask value
 * - Adding flags (including idempotence)
 * - Removing flags
 * - Combining multiple flags
 * - Toggling flags
 * - Clearing all flags
 * - Checking if a flag is present or not
 * - Iterating over active flags and converting to an array
 * - Serialization and deserialization via JSON
 */
final class FlagManagerTest extends TestCase
{
    public function testInitialMaskIsZero(): void
    {
        $manager = new FlagManager();
        $this->assertSame(0, $manager->getMask());
    }

    public function testAddFlag(): void
    {
        $manager = new FlagManager();
        $manager->add(Permission::SendMessages);
        $this->assertSame(1, $manager->getMask());

        $manager->add(Permission::AddUsers);
        $this->assertSame(5, $manager->getMask());
    }

    public function testAddFlagIdempotence(): void
    {
        $manager = new FlagManager();
        $manager->add(Permission::SendMessages)
            ->add(Permission::SendMessages);
        $this->assertSame(1, $manager->getMask());
    }

    public function testAddFlagFromAnotherEnum(): void
    {
        $manager = new FlagManager();
        $manager->add(Permission::SendMessages);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The provided flag is not part of the current enum.');

        $manager->add(XPermission::InstallFile);
    }

    public function testRemoveFlag(): void
    {
        $manager = new FlagManager();
        $manager->add(Permission::SendMessages)
            ->add(Permission::AddUsers);
        $manager->remove(Permission::AddUsers);
        $this->assertSame(1, $manager->getMask());

        $manager->remove(Permission::DeleteMessages);
        $this->assertSame(1, $manager->getMask());
    }

    public function testCombineFlags(): void
    {
        $manager = new FlagManager();
        $manager->combine(
            Permission::SendMessages,
            Permission::AddUsers,
            Permission::DeleteMessages
        );

        $this->assertSame(7, $manager->getMask());
    }

    public function testToggleFlags(): void
    {
        $manager = new FlagManager();

        $manager->add(Permission::SendMessages)
            ->add(Permission::AddUsers)
            ->add(Permission::PinMessages);
        $this->assertSame(21, $manager->getMask());

        $manager->toggle(
            Permission::SendMessages,
            Permission::DeleteMessages,
            Permission::AddUsers,
            Permission::RemoveUsers
        );
        $this->assertSame(26, $manager->getMask());
    }

    public function testClearFlags(): void
    {
        $manager = new FlagManager();
        $manager->add(Permission::SendMessages)
            ->add(Permission::AddUsers);
        $manager->clear();
        $this->assertSame(0, $manager->getMask());
    }

    public function testHasAndDoesntHave(): void
    {
        $manager = new FlagManager();
        $manager->add(Permission::SendMessages)
            ->add(Permission::AddUsers);
        $this->assertTrue($manager->has(Permission::SendMessages));
        $this->assertFalse($manager->has(Permission::DeleteMessages));
        $this->assertTrue($manager->doesntHave(Permission::DeleteMessages));
        $this->assertFalse($manager->doesntHave(Permission::SendMessages));

        $this->assertFalse($manager->has(XPermission::InstallFile));
        $this->assertTrue($manager->doesntHave(XPermission::InstallFile));
    }

    public function testIteratorAndToArray(): void
    {
        $manager = new FlagManager();
        $manager->add(Permission::SendMessages)
            ->add(Permission::AddUsers);
        $flagsArray = $manager->toArray();
        $this->assertIsArray($flagsArray);
        $this->assertCount(2, $flagsArray);

        foreach ($manager as $flag) {
            $this->assertInstanceOf(Permission::class, $flag);
        }
    }

    public function testSerializationAndDeserialization(): void
    {
        $manager = new FlagManager();
        $manager->add(Permission::SendMessages)
            ->add(Permission::AddUsers);
        $serialized = $manager->serialize();
        $this->assertIsString($serialized);

        $unserializedManager = new FlagManager();
        $unserializedManager->unserialize($serialized);

        $this->assertInstanceOf(FlagManager::class, $unserializedManager);
        $this->assertSame($manager->getMask(), $unserializedManager->getMask());
    }

    /**
     * @throws JsonException
     */
    public function testUnserializeExcludingEnum(): void
    {
        $manager = new FlagManager();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid unserialized data.');
        $manager->unserialize(json_encode([
            'flags' => [Permission::SendMessages, Permission::AddUsers]
        ]));
    }

    /**
     * @throws JsonException
     */
    public function testUnserializeExcludingFlags(): void
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
    public function testUnserializeNonBitwiseEnumClass(): void
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
    public function testUnserializeNonEnumClass(): void
    {
        $manager = new FlagManager();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage("Invalid enum class: " . PrehistoricEnum::class);
        $manager->unserialize(json_encode([
            'enum' => PrehistoricEnum::class,
            'flags' => [PrehistoricEnum::BOUNTY, PrehistoricEnum::TWIX]
        ]));
    }

    /**
     * @throws JsonException
     * @throws Exception
     */
    public function testUnserializeWithBrokenMask(): void
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

    public function testPrintable(): void
    {
        $manager = new FlagManager();
        $manager->add(Permission::SendMessages)
            ->add(Permission::AddUsers);
        $this->assertSame('5', (string)$manager);

        $manager->toggle(
            Permission::SendMessages,
            Permission::DeleteMessages,
            Permission::AddUsers,
            Permission::RemoveUsers
        );
        $this->assertSame('10', (string)$manager);

        $manager->clear();
        $this->assertSame('0', (string)$manager);

        $manager->add(Permission::SendMessages)
            ->add(Permission::AddUsers)
            ->add(Permission::PinMessages);
        $this->assertSame('21', (string)$manager);
    }

    public function testJsonSerialize(): void
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
