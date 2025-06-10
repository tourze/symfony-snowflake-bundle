<?php

namespace Tourze\SnowflakeBundle\Tests;

use PHPUnit\Framework\TestCase;
use Tourze\SnowflakeBundle\SnowflakeBundle;

class SnowflakeBundleTest extends TestCase
{
    public function test_getPath_returnsCorrectPath(): void
    {
        $bundle = new SnowflakeBundle();
        $expectedPath = dirname(__DIR__) . '/src';

        $this->assertEquals($expectedPath, $bundle->getPath());
    }

    public function test_implements_bundleInterface(): void
    {
        $bundle = new SnowflakeBundle();

        $this->assertInstanceOf(\Symfony\Component\HttpKernel\Bundle\BundleInterface::class, $bundle);
    }
}
