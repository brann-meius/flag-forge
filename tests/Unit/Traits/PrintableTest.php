<?php

declare(strict_types=1);

namespace Meius\FlagForge\Tests\Unit\Traits;

use Meius\FlagForge\FlagManager;
use Meius\FlagForge\Tests\Unit\DataProviders\PrintableDataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * This test suite covers the basic functionalities of the FlagManager class:
 *
 * - Converting the flag manager to a string
 */
class PrintableTest extends TestCase
{
    #[Test, DataProviderExternal(PrintableDataProvider::class, 'provideCases')]
    public function convertingToStringBehavior(FlagManager $manager, string $expect): void
    {
        $this->assertSame($expect, (string)$manager);
    }
}
