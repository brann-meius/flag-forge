<?php

declare(strict_types=1);

namespace Meius\FlagForge\Tests\Unit\Traits;

use Meius\FlagForge\FlagManager;
use Meius\FlagForge\Tests\Support\Permission;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * This test suite covers the basic functionalities of the FlagManager class:
 *
 * - Iterating over active flags and converting to an array
 */
class WalkableTest extends TestCase
{
    #[Test]
    public function iteratorBehavior()
    {
        $manager = (new FlagManager())
            ->add(Permission::SendMessages)
            ->add(Permission::AddUsers);

        foreach ($manager as $flag) {
            $this->assertInstanceOf(Permission::class, $flag);
        }

        return $manager;
    }

    #[Test, Depends('iteratorBehavior')]
    public function toArrayBehavior(FlagManager $manager): void
    {
        $this->assertIsArray($manager->toArray());
        $this->assertCount(2, $manager->toArray());
    }
}
