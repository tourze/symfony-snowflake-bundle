<?php

namespace Tourze\SnowflakeBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\SnowflakeBundle\Service\ResolverFactory;
use Tourze\SnowflakeBundle\Service\Snowflake;
use Tourze\SnowflakeBundle\SnowflakeBundle;

/**
 * @internal
 */
#[CoversClass(SnowflakeBundle::class)]
#[RunTestsInSeparateProcesses]
final class SnowflakeIntegrationTest extends AbstractIntegrationTestCase
{
    public function testSnowflakeServiceIsRegisteredInContainer(): void
    {
        $container = self::getContainer();

        $this->assertTrue($container->has(Snowflake::class));

        $snowflake = self::getService(Snowflake::class);
        $this->assertInstanceOf(Snowflake::class, $snowflake);
    }

    public function testResolverFactoryServiceIsRegisteredInContainer(): void
    {
        $container = self::getContainer();

        $this->assertTrue($container->has(ResolverFactory::class));

        $resolverFactory = $container->get(ResolverFactory::class);
        $this->assertInstanceOf(ResolverFactory::class, $resolverFactory);
    }

    public function testSnowflakeServiceGeneratesValidIds(): void
    {
        $snowflake = self::getService(Snowflake::class);

        $id1 = $snowflake->id();
        $id2 = $snowflake->id();

        $this->assertIsNumeric($id1);
        $this->assertIsNumeric($id2);

        $this->assertNotEquals($id1, $id2);

        $this->assertGreaterThan($id1, $id2);
    }

    public function testSnowflakeServiceGeneratesUniqueIdsInBatch(): void
    {
        $snowflake = self::getService(Snowflake::class);

        $ids = [];
        $count = 50;

        for ($i = 0; $i < $count; ++$i) {
            $ids[] = $snowflake->id();
        }

        $uniqueIds = array_unique($ids);
        $this->assertCount($count, $uniqueIds);

        for ($i = 1; $i < $count; ++$i) {
            $this->assertGreaterThan($ids[$i - 1], $ids[$i]);
        }
    }

    public function testSnowflakeServiceIsSingleton(): void
    {
        $snowflake1 = self::getService(Snowflake::class);
        $snowflake2 = self::getService(Snowflake::class);

        $this->assertSame($snowflake1, $snowflake2);
    }

    public function testResolverFactoryServiceIsSingleton(): void
    {
        $factory1 = self::getService(ResolverFactory::class);
        $factory2 = self::getService(ResolverFactory::class);

        $this->assertSame($factory1, $factory2);
    }

    public function testServicesLifecycle(): void
    {
        $snowflake = self::getService(Snowflake::class);
        $resolverFactory = self::getService(ResolverFactory::class);

        $id1 = $snowflake->id();
        $this->assertIsNumeric($id1);

        $snowflake2 = self::getService(Snowflake::class);
        $this->assertSame($snowflake, $snowflake2);

        $id2 = $snowflake2->id();
        $this->assertGreaterThan($id1, $id2);
    }

    public function testSnowflakeServiceLargeBatchGeneration(): void
    {
        $snowflake = self::getService(Snowflake::class);

        $startTime = microtime(true);
        $ids = [];
        $count = 500;

        for ($i = 0; $i < $count; ++$i) {
            $ids[] = $snowflake->id();
        }

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        $uniqueIds = array_unique($ids);
        $this->assertCount($count, $uniqueIds);

        foreach ($ids as $id) {
            $this->assertIsNumeric($id);
            $this->assertGreaterThan(0, intval($id));
        }

        $this->assertLessThan(0.5, $duration, "生成{$count}个ID耗时过长: {$duration}秒");
    }

    public function testServicesWorkInDifferentContainerStates(): void
    {
        $snowflake = self::getService(Snowflake::class);
        $id1 = $snowflake->id();

        $this->assertIsNumeric($id1);

        $id2 = $snowflake->id();
        $this->assertGreaterThan($id1, $id2);

        $snowflakeNew = self::getService(Snowflake::class);
        $this->assertSame($snowflake, $snowflakeNew);

        $id3 = $snowflakeNew->id();
        $this->assertGreaterThan($id2, $id3);

        $allIds = [$id1, $id2, $id3];
        $uniqueIds = array_unique($allIds);
        $this->assertCount(3, $uniqueIds);
    }

    public function testBundleConfigurationLoaded(): void
    {
        $container = self::getContainer();

        $this->assertTrue($container->has(Snowflake::class));
        $this->assertTrue($container->has(ResolverFactory::class));

        $snowflake = self::getService(Snowflake::class);
        $resolverFactory = self::getService(ResolverFactory::class);

        $this->assertInstanceOf(Snowflake::class, $snowflake);
        $this->assertInstanceOf(ResolverFactory::class, $resolverFactory);

        $id = $snowflake->id();
        $this->assertIsNumeric($id);
    }

    public function testSnowflakeServiceSimulatedConcurrency(): void
    {
        $snowflake = self::getService(Snowflake::class);

        $ids = [];
        $iterations = 20;

        for ($i = 0; $i < $iterations; ++$i) {
            $ids[] = $snowflake->id();
            if (0 === $i % 5) {
                usleep(100);
            }
        }

        $uniqueIds = array_unique($ids);
        $this->assertCount($iterations, $uniqueIds);

        for ($i = 1; $i < $iterations; ++$i) {
            $this->assertGreaterThanOrEqual($ids[$i - 1], $ids[$i]);
        }
    }

    protected function onSetUp(): void
    {
        // 无需特殊设置
    }
}
