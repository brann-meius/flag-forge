<?php

declare(strict_types=1);

namespace Meius\FlagForge\Tests\Support;

use Meius\FlagForge\Contracts\Bitwiseable;

enum XPermission: int implements Bitwiseable
{
    case InstallFile = 1 << 0;
}
