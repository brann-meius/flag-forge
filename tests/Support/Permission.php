<?php

declare(strict_types=1);

namespace Meius\FlagForge\Tests\Support;

use Meius\FlagForge\Contracts\Bitwiseable;

enum Permission: int implements Bitwiseable
{
    case SendMessages = 1 << 0;
    case DeleteMessages = 1 << 1;
    case AddUsers = 1 << 2;
    case RemoveUsers = 1 << 3;
    case PinMessages = 1 << 4;
    case ManageChat = 1 << 5;
    case ManageModerators = 1 << 6;
}
