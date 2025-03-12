<?php

declare(strict_types=1);

namespace Meius\FlagForge\Tests\Unit\DataProviders;

use Generator;
use Meius\FlagForge\FlagManager;
use Meius\FlagForge\Tests\Support\Permission;

class PrintableDataProvider
{
    public static function provideCases(): Generator
    {
        $manager = (new FlagManager())->combine(
            Permission::SendMessages,
            Permission::AddUsers
        );
        yield [$manager, '5'];

        $manager = (clone $manager)->toggle(
            Permission::SendMessages,
            Permission::DeleteMessages,
            Permission::AddUsers,
            Permission::RemoveUsers
        );
        yield [$manager, '10'];

        $manager = (clone $manager)->clear();
        yield [$manager, '0'];

        $manager = (clone $manager)->add(Permission::SendMessages)
            ->add(Permission::AddUsers)
            ->add(Permission::PinMessages);
        yield [$manager, '21'];
    }
}
