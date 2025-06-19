<?php

namespace Tourze\SnowflakeBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Tourze\IntegrationTestKernel\IntegrationTestKernel;
use Tourze\SnowflakeBundle\Service\ResolverFactory;
use Tourze\SnowflakeBundle\Service\Snowflake;
use Tourze\SnowflakeBundle\SnowflakeBundle;

class SnowflakeIntegrationTest extends KernelTestCase
{
    protected static function createKernel(array $options = []): KernelInterface
    {
        $env = $options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test';
        $debug = $options['debug'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true;

        return new IntegrationTestKernel($env, $debug, [
            SnowflakeBundle::class => ['all' => true],
        ]);
    }

    protected function setUp(): void
    {
        self::bootKernel();
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
        parent::tearDown();
    }

    public function test_snowflakeService_isRegisteredInContainer(): void
    {
        $container = static::getContainer();

        $this->assertTrue($container->has('snc_redis.phpredis_factory'));
        $this->assertTrue($container->has('snc_redis.snowflake'));
        $this->assertTrue($container->has(Snowflake::class));

        $snowflake = $container->get(Snowflake::class);
        $this->assertInstanceOf(Snowflake::class, $snowflake);
    }

    public function test_resolverFactoryService_isRegisteredInContainer(): void
    {
        $container = static::getContainer();

        $this->assertTrue($container->has(ResolverFactory::class));

        $resolverFactory = $container->get(ResolverFactory::class);
        $this->assertInstanceOf(ResolverFactory::class, $resolverFactory);
    }

    public function test_snowflakeService_generatesValidIds(): void
    {
        $container = static::getContainer();
        $snowflake = $container->get(Snowflake::class);

        $id1 = $snowflake->id();
        $id2 = $snowflake->id();

        // 验证生成的ID是字符串

        // 验证ID是数字字符串
        $this->assertIsNumeric($id1);
        $this->assertIsNumeric($id2);

        // 验证ID的唯一性
        $this->assertNotEquals($id1, $id2);

        // 验证ID是递增的
        $this->assertGreaterThan($id1, $id2);
    }

    public function test_snowflakeService_generatesUniqueIdsInBatch(): void
    {
        $container = static::getContainer();
        $snowflake = $container->get(Snowflake::class);

        $ids = [];
        $count = 50;

        for ($i = 0; $i < $count; $i++) {
            $ids[] = $snowflake->id();
        }

        // 验证所有ID都是唯一的
        $uniqueIds = array_unique($ids);
        $this->assertCount($count, $uniqueIds);

        // 验证ID是按顺序生成的
        for ($i = 1; $i < $count; $i++) {
            $this->assertGreaterThan($ids[$i - 1], $ids[$i]);
        }
    }

    /**
     * 测试服务是否为单例
     */
    public function test_snowflakeService_isSingleton(): void
    {
        $container = static::getContainer();

        $snowflake1 = $container->get(Snowflake::class);
        $snowflake2 = $container->get(Snowflake::class);

        // 应该是同一个实例
        $this->assertSame($snowflake1, $snowflake2);
    }

    /**
     * 测试ResolverFactory服务是否为单例
     */
    public function test_resolverFactoryService_isSingleton(): void
    {
        $container = static::getContainer();

        $factory1 = $container->get(ResolverFactory::class);
        $factory2 = $container->get(ResolverFactory::class);

        // 应该是同一个实例
        $this->assertSame($factory1, $factory2);
    }

    /**
     * 测试服务的生命周期
     */
    public function test_services_lifecycle(): void
    {
        $container = static::getContainer();

        // 获取服务实例
        $snowflake = $container->get(Snowflake::class);
        $resolverFactory = $container->get(ResolverFactory::class);

        // 验证服务可以正常工作
        $id1 = $snowflake->id();
        $this->assertIsNumeric($id1);

        // 再次获取服务，应该是同一个实例
        $snowflake2 = $container->get(Snowflake::class);
        $this->assertSame($snowflake, $snowflake2);

        // 生成的ID应该继续递增
        $id2 = $snowflake2->id();
        $this->assertGreaterThan($id1, $id2);
    }

    /**
     * 测试大批量ID生成的性能和唯一性
     */
    public function test_snowflakeService_largeBatchGeneration(): void
    {
        $container = static::getContainer();
        $snowflake = $container->get(Snowflake::class);

        $startTime = microtime(true);
        $ids = [];
        $count = 500; // 生成500个ID

        for ($i = 0; $i < $count; $i++) {
            $ids[] = $snowflake->id();
        }

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // 验证所有ID都是唯一的
        $uniqueIds = array_unique($ids);
        $this->assertCount($count, $uniqueIds);

        // 验证ID的格式
        foreach ($ids as $id) {
            $this->assertIsNumeric($id);
            $this->assertGreaterThan(0, intval($id));
        }

        // 验证性能（500个ID应该在0.5秒内生成完成）
        $this->assertLessThan(0.5, $duration, "生成{$count}个ID耗时过长: {$duration}秒");
    }

    /**
     * 测试服务在不同的容器状态下的行为
     */
    public function test_services_workInDifferentContainerStates(): void
    {
        $container = static::getContainer();

        // 首次获取服务
        $snowflake = $container->get(Snowflake::class);
        $id1 = $snowflake->id();

        // 验证初始ID
        $this->assertIsNumeric($id1);

        // 再次使用服务
        $id2 = $snowflake->id();
        $this->assertGreaterThan($id1, $id2);

        // 获取新的服务实例引用
        $snowflakeNew = $container->get(Snowflake::class);
        $this->assertSame($snowflake, $snowflakeNew);

        // 继续生成ID
        $id3 = $snowflakeNew->id();
        $this->assertGreaterThan($id2, $id3);

        // 验证所有ID都不相同
        $allIds = [$id1, $id2, $id3];
        $uniqueIds = array_unique($allIds);
        $this->assertCount(3, $uniqueIds);
    }

    /**
     * 测试Bundle的配置是否正确加载
     */
    public function test_bundle_configurationLoaded(): void
    {
        $container = static::getContainer();

        // 验证Bundle相关的服务都已正确注册
        $this->assertTrue($container->has(Snowflake::class));
        $this->assertTrue($container->has(ResolverFactory::class));

        // 验证服务是否可以正常实例化和工作
        $snowflake = $container->get(Snowflake::class);
        $resolverFactory = $container->get(ResolverFactory::class);

        $this->assertInstanceOf(Snowflake::class, $snowflake);
        $this->assertInstanceOf(ResolverFactory::class, $resolverFactory);

        // 验证服务功能
        $id = $snowflake->id();
        $this->assertIsNumeric($id);
    }

    /**
     * 测试并发访问场景（模拟）
     */
    public function test_snowflakeService_simulatedConcurrency(): void
    {
        $container = static::getContainer();
        $snowflake = $container->get(Snowflake::class);

        $ids = [];
        $iterations = 20;

        // 模拟快速连续调用
        for ($i = 0; $i < $iterations; $i++) {
            $ids[] = $snowflake->id();
            // 添加微小延迟模拟实际使用场景
            if ($i % 5 === 0) {
                usleep(100); // 0.1毫秒延迟
            }
        }

        // 验证唯一性
        $uniqueIds = array_unique($ids);
        $this->assertCount($iterations, $uniqueIds);

        // 验证递增性
        for ($i = 1; $i < $iterations; $i++) {
            $this->assertGreaterThanOrEqual($ids[$i - 1], $ids[$i]);
        }
    }
}
