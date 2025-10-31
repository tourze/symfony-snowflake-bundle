<?php

declare(strict_types=1);

namespace Tourze\SnowflakeBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use Tourze\SnowflakeBundle\SnowflakeBundle;

/**
 * @internal
 */
#[CoversClass(SnowflakeBundle::class)]
#[RunTestsInSeparateProcesses]
final class SnowflakeBundleTest extends AbstractBundleTestCase
{
}
