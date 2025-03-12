<?php

declare(strict_types=1);

namespace Meius\FlagForge\Tests\Unit\Traits;

use Meius\FlagForge\Contracts\Bitwiseable;
use Meius\FlagForge\FlagManager;
use Meius\FlagForge\Tests\Unit\DataProviders\ComparableDataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * This test suite covers the basic functionalities of the FlagManager class:
 *
 * - Checking if a flag is present or not
 */
class ComparableTest extends TestCase
{
    #[Test, DataProviderExternal(ComparableDataProvider::class, 'provideCases')]
    public function hasMethodBehavior(FlagManager $manager, Bitwiseable $flag, bool $result): void
    {
        $this->assertSame($result, $manager->has($flag));
    }

    #[Test, DataProviderExternal(ComparableDataProvider::class, 'provideCases')]
    public function doesntHaveMethodBehavior(FlagManager $manager, Bitwiseable $flag, bool $result): void
    {
        $this->assertSame(!$result, $manager->doesntHave($flag));
    }
}
