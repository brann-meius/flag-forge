<?php

declare(strict_types=1);

namespace Meius\FlagForge\Tests\Unit;

use InvalidArgumentException;
use Meius\FlagForge\Tests\Support\Permission;
use Meius\FlagForge\Tests\Support\XPermission;
use Meius\FlagForge\Tests\Unit\DataProviders\FlagManagerDataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Meius\FlagForge\FlagManager;

/**
 * This test suite covers the basic functionalities of the FlagManager class:
 * - Initial mask value
 * - Adding flags (including idempotence)
 * - Removing flags
 * - Combining multiple flags
 * - Toggling flags
 * - Clearing all flags
 */
class FlagManagerTest extends TestCase
{
    #[Test]
    public function initialMaskIsZero(): void
    {
        $this->assertSame(0, (new FlagManager())->getMask());
    }

    #[Test, DataProviderExternal(FlagManagerDataProvider::class, 'provideAddMethodCases')]
    public function addBehavior(array $flags, int $expected): FlagManager
    {
        $manager = new FlagManager();

        foreach ($flags as $flag) {
            $manager->add($flag);
        }

        $this->assertSame($expected, $manager->getMask());

        return $manager;
    }

    #[Test]
    public function addFlagFromAnotherEnum(): void
    {
        $manager = (new FlagManager())->add(Permission::SendMessages);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The provided flag is not part of the current enum.');

        $manager->add(XPermission::InstallFile);
    }

    #[Test, DataProviderExternal(FlagManagerDataProvider::class, 'provideAddMethodCases')]
    public function removeBehavior(array $flags): void
    {
        $manager = new FlagManager();

        foreach ($flags as $flag) {
            $manager->add($flag);
        }

        $mask = $manager->getMask();
        $cases = [
            Permission::DeleteMessages,
            Permission::AddUsers,
            Permission::SendMessages,
        ];

        /** @var Permission $case */
        foreach ($cases as $case) {
            if ($manager->has($case)) {
                $manager->remove($case);

                $this->assertSame($mask - $case->value, $manager->getMask());

                $mask = $manager->getMask();
            }
        }
    }

    #[Test]
    public function combineBehavior(): void
    {
        $manager = (new FlagManager())->combine(
            Permission::SendMessages,
            Permission::AddUsers,
            Permission::DeleteMessages
        );

        $this->assertSame(7, $manager->getMask());
    }

    #[Test]
    public function toggleBehavior(): void
    {
        $manager = (new FlagManager())
            ->add(Permission::SendMessages)
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

    #[Test, DataProviderExternal(FlagManagerDataProvider::class, 'provideAddMethodCases')]
    public function clearBehavior(array $flags): void
    {
        $manager = new FlagManager();

        foreach ($flags as $flag) {
            $manager->add($flag);
        }

        $this->assertSame(0, $manager->clear()->getMask());
    }
}
