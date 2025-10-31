<?php

namespace Tourze\SnowflakeBundle\Tests\Service;

use Godruoyi\Snowflake\RandomSequenceResolver;
use Godruoyi\Snowflake\RedisSequenceResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\SnowflakeBundle\Service\RedisResolverFactory;
use Tourze\SnowflakeBundle\Service\ResolverFactoryInterface;

/**
 * @internal
 */
#[CoversClass(RedisResolverFactory::class)]
final class RedisResolverFactoryTest extends TestCase
{
    protected function onSetUp(): void
    {
    }

    public function testImplementsResolverFactoryInterface(): void
    {
        // 必须使用 Redis 具体类的原因：
        // 1. RedisResolverFactory 构造函数明确要求 \Redis 类型，不是接口
        // 2. 这是第三方库和专用连接机制的强制要求，无法使用抽象类或接口
        // 3. 需要验证工厂类实现了正确的接口
        $redis = $this->createMock(\Redis::class);
        $factory = new RedisResolverFactory($redis);

        $this->assertInstanceOf(ResolverFactoryInterface::class, $factory);
    }

    public function testResolverWithWorkingRedis(): void
    {
        // 必须使用 Redis 具体类的原因：
        // 1. RedisResolverFactory 构造函数明确要求 \Redis 类型，不是接口
        // 2. RedisSequenceResolver 需要 Redis 的特定方法（ping、eval）
        // 3. 这是第三方库的强制要求，无法使用抽象类或接口
        $redis = $this->createMock(\Redis::class);
        $redis->method('ping')->willReturn(true);

        $factory = new RedisResolverFactory($redis);
        $resolver = $factory->resolver();

        $this->assertInstanceOf(RedisSequenceResolver::class, $resolver);
    }

    public function testResolverWithFailingRedis(): void
    {
        // 必须使用 Redis 具体类的原因：
        // 1. RedisResolverFactory 构造函数明确要求 \Redis 类型，不是接口
        // 2. 需要测试 Redis 连接失败时的回退逻辑
        // 3. 这是第三方库的强制要求，无法使用抽象类或接口
        $redis = $this->createMock(\Redis::class);
        $redis->method('ping')->willThrowException(new \Exception('Redis connection failed'));

        $factory = new RedisResolverFactory($redis);
        $resolver = $factory->resolver();

        $this->assertInstanceOf(RandomSequenceResolver::class, $resolver);
    }
}
