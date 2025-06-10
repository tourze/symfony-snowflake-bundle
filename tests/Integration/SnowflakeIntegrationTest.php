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
        $this->assertIsString($id1);
        $this->assertIsString($id2);

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
}
