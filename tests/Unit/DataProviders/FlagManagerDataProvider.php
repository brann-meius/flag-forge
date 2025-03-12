<?php

declare(strict_types=1);

namespace Meius\FlagForge\Tests\Unit\DataProviders;

use Meius\FlagForge\Tests\Support\Permission;

class FlagManagerDataProvider
{
    public static function provideAddMethodCases(): array
    {
        return [
            [
                [
                    Permission::SendMessages
                ],
                1,
            ],
            [
                [
                    Permission::SendMessages,
                    Permission::AddUsers,
                    Permission::PinMessages,
                ],
                21,
            ],
            [
                [
                    Permission::SendMessages,
                    Permission::SendMessages,
                ],
                1,
            ],
        ];
    }
}
