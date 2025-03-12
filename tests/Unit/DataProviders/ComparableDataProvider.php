<?php

declare(strict_types=1);

namespace Meius\FlagForge\Tests\Unit\DataProviders;

use Meius\FlagForge\FlagManager;
use Meius\FlagForge\Tests\Support\Permission;
use Meius\FlagForge\Tests\Support\XPermission;

class ComparableDataProvider
{
    public static function provideCases(): array
    {
        $manager = (new FlagManager())->combine(
            Permission::SendMessages,
            Permission::AddUsers
        );

        return [
            [$manager, Permission::SendMessages, true],
            [$manager, Permission::DeleteMessages, false],
            [$manager, XPermission::InstallFile, false],
        ];
    }
}
