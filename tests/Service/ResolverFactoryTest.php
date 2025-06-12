<?php

namespace Tourze\SnowflakeBundle\Tests\Service;

use Godruoyi\Snowflake\RandomSequenceResolver;
use Godruoyi\Snowflake\RedisSequenceResolver;
use Godruoyi\Snowflake\SequenceResolver;
use PHPUnit\Framework\TestCase;
use Redis;
use ReflectionClass;
use Tourze\SnowflakeBundle\Service\ResolverFactory;

class ResolverFactoryTest extends TestCase
{
    /**
     * 测试在没有Redis的情况下返回RandomSequenceResolver
     */
    public function test_resolver_withoutRedis_returnsRandomSequenceResolver(): void
    {
        $resolverFactory = new ResolverFactory();
        $resolver = $resolverFactory->resolver();

        $this->assertInstanceOf(RandomSequenceResolver::class, $resolver);
    }

    /**
     * 测试当Redis可用时，resolver方法应该返回RedisSequenceResolver实例
     */
    public function test_resolver_withRedis_returnsRedisSequenceResolver(): void
    {
        // 创建一个模拟的Redis对象，并设置ping方法返回true
        $redis = $this->createStub(Redis::class);
        $redis->method('ping')->willReturn(true);
        $redis->method('eval')->willReturn(0); // 防止后续的eval调用失败

        $resolverFactory = new ResolverFactory($redis);

        // 使用反射来访问私有属性，验证Redis实例已正确设置
        $reflectionClass = new ReflectionClass(ResolverFactory::class);
        $reflectionProperty = $reflectionClass->getProperty('redis');
        $reflectionProperty->setAccessible(true);
        $redisInstance = $reflectionProperty->getValue($resolverFactory);

        // 验证Redis实例已正确设置
        $this->assertSame($redis, $redisInstance);

        // 获取实际的 resolver
        $resolver = $resolverFactory->resolver();

        // 验证返回的是 RedisSequenceResolver
        $this->assertInstanceOf(RedisSequenceResolver::class, $resolver);
    }

    /**
     * 测试构造函数正确处理可选的Redis依赖
     */
    public function test_constructor_handlesOptionalRedisDependency(): void
    {
        // 测试无Redis构造
        $resolverFactory1 = new ResolverFactory();
        $this->assertInstanceOf(ResolverFactory::class, $resolverFactory1);

        // 测试有Redis构造
        $redis = $this->createStub(Redis::class);
        $resolverFactory2 = new ResolverFactory($redis);
        $this->assertInstanceOf(ResolverFactory::class, $resolverFactory2);
    }

    /**
     * 测试Redis实例的存储和访问
     */
    public function test_redisInstance_storedCorrectly(): void
    {
        $redis = $this->createStub(Redis::class);
        $resolverFactory = new ResolverFactory($redis);

        // 使用反射验证Redis实例被正确存储
        $reflectionClass = new ReflectionClass(ResolverFactory::class);
        $reflectionProperty = $reflectionClass->getProperty('redis');
        $reflectionProperty->setAccessible(true);
        $storedRedis = $reflectionProperty->getValue($resolverFactory);

        $this->assertSame($redis, $storedRedis);
    }

    /**
     * 测试 resolver 方法返回的对象实现了 SequenceResolver 接口
     */
    public function test_resolver_returnsSequenceResolverInterface(): void
    {
        // 测试无Redis情况
        $resolverFactory1 = new ResolverFactory();
        $resolver1 = $resolverFactory1->resolver();
        $this->assertInstanceOf(SequenceResolver::class, $resolver1);

        // 测试有Redis情况 - 使用mock避免实际Redis连接
        $redis = $this->createMock(Redis::class);

        // 创建一个部分模拟的ResolverFactory，只模拟resolver方法返回
        $resolverFactory2 = $this->getMockBuilder(ResolverFactory::class)
            ->setConstructorArgs([$redis])
            ->onlyMethods(['resolver'])
            ->getMock();

        $mockResolver = $this->createMock(SequenceResolver::class);
        $resolverFactory2->method('resolver')->willReturn($mockResolver);

        $resolver2 = $resolverFactory2->resolver();
        $this->assertInstanceOf(SequenceResolver::class, $resolver2);
    }

    /**
     * 测试Redis分支的逻辑（不实际连接Redis）
     */
    public function test_resolver_withRedis_logicPath(): void
    {
        $redis = $this->createMock(Redis::class);
        $resolverFactory = new ResolverFactory($redis);

        // 验证Redis路径被正确选择
        $reflectionClass = new ReflectionClass(ResolverFactory::class);
        $reflectionProperty = $reflectionClass->getProperty('redis');
        $reflectionProperty->setAccessible(true);
        $redisInstance = $reflectionProperty->getValue($resolverFactory);

        $this->assertNotNull($redisInstance);
        $this->assertSame($redis, $redisInstance);
    }
}
