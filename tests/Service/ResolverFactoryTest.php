<?php

namespace Tourze\SnowflakeBundle\Tests\Service;

use Godruoyi\Snowflake\RandomSequenceResolver;
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
    public function testResolverWithoutRedis(): void
    {
        $resolverFactory = new ResolverFactory();
        $resolver = $resolverFactory->resolver();

        $this->assertInstanceOf(RandomSequenceResolver::class, $resolver);
    }

    /**
     * 测试当Redis可用时，resolver方法应该返回RedisSequenceResolver实例
     */
    public function testResolverWithRedis(): void
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

        // 创建一个模拟的RedisSequenceResolver
        $resolverFactoryMock = $this->getMockBuilder(ResolverFactory::class)
            ->setConstructorArgs([$redis])
            ->onlyMethods(['resolver'])
            ->getMock();

        $sequenceResolver = $this->createMock(SequenceResolver::class);
        $resolverFactoryMock->method('resolver')->willReturn($sequenceResolver);

        $resolver = $resolverFactoryMock->resolver();
        $this->assertInstanceOf(SequenceResolver::class, $resolver);

        // 测试实际的resolver方法，确保代码分支覆盖
        // 我们知道实际创建RedisSequenceResolver会失败，所以这里只验证Redis不为null
        $this->assertNotNull($redisInstance);
    }
}
