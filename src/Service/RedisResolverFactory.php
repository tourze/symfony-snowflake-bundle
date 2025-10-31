<?php

declare(strict_types=1);

namespace Tourze\SnowflakeBundle\Service;

use Godruoyi\Snowflake\SequenceResolver;
use Tourze\RedisDedicatedConnectionBundle\Attribute\WithDedicatedConnection;

/**
 * 生产环境的 ResolverFactory，具有专用 Redis 连接
 */
#[WithDedicatedConnection(channel: 'snowflake')]
class RedisResolverFactory implements ResolverFactoryInterface
{
    private readonly ResolverFactory $resolverFactory;

    public function __construct(
        \Redis $redis,
    ) {
        $this->resolverFactory = new ResolverFactory($redis);
    }

    public function resolver(): SequenceResolver
    {
        return $this->resolverFactory->resolver();
    }
}
