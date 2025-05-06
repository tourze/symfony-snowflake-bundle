<?php

namespace Tourze\SnowflakeBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tourze\SnowflakeBundle\Service\ResolverFactory;
use Tourze\SnowflakeBundle\Service\Snowflake;

/**
 * 测试SnowflakeBundle与Symfony框架的集成
 */
class SnowflakeIntegrationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return IntegrationTestKernel::class;
    }

    protected function setUp(): void
    {
        // 启动内核
        self::bootKernel();
    }

    /**
     * 测试Snowflake服务是否正确注册
     */
    public function testSnowflakeServiceRegistration(): void
    {
        $container = static::getContainer();

        // 测试Snowflake服务是否存在
        $this->assertTrue($container->has(Snowflake::class));

        // 测试ResolverFactory服务是否存在
        $this->assertTrue($container->has(ResolverFactory::class));

        // 获取Snowflake服务实例
        $snowflake = $container->get(Snowflake::class);
        $this->assertInstanceOf(Snowflake::class, $snowflake);

        // 获取ResolverFactory服务实例
        $resolverFactory = $container->get(ResolverFactory::class);
        $this->assertInstanceOf(ResolverFactory::class, $resolverFactory);
    }

    /**
     * 测试Snowflake服务是否能正确生成雪花ID
     */
    public function testSnowflakeIdGeneration(): void
    {
        $container = static::getContainer();
        $snowflake = $container->get(Snowflake::class);

        // 生成一个雪花ID
        $id = $snowflake->id();

        // 验证ID是一个非空字符串
        $this->assertIsString($id);
        $this->assertNotEmpty($id);

        // 验证ID是一个数字字符串
        $this->assertIsNumeric($id);

        // 验证ID的长度（应该至少有10位数）
        $this->assertGreaterThan(10, strlen($id));
    }

    /**
     * 测试在短时间内多次调用id方法返回唯一ID
     */
    public function testSnowflakeIdUniqueness(): void
    {
        $container = static::getContainer();
        $snowflake = $container->get(Snowflake::class);

        $ids = [];
        $count = 10; // 生成10个ID

        for ($i = 0; $i < $count; $i++) {
            $ids[] = $snowflake->id();
        }

        // 验证生成的所有ID都是唯一的
        $uniqueIds = array_unique($ids);
        $this->assertCount($count, $uniqueIds);

        // 验证ID是按顺序生成的（每个ID应该大于前一个）
        for ($i = 1; $i < $count; $i++) {
            $this->assertGreaterThan($ids[$i - 1], $ids[$i]);
        }
    }
}
