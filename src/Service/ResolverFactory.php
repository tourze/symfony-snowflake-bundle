<?php

declare(strict_types=1);

namespace Tourze\SnowflakeBundle\Service;

use Godruoyi\Snowflake\RandomSequenceResolver;
use Godruoyi\Snowflake\RedisSequenceResolver;
use Godruoyi\Snowflake\SequenceResolver;

class ResolverFactory implements ResolverFactoryInterface
{
    public function __construct(
        private readonly ?\Redis $redis = null,
    ) {
    }

    public function resolver(): SequenceResolver
    {
        if (null !== $this->redis) {
            try {
                // 尝试验证 Redis 连接是否可用
                $this->redis->ping();

                return new RedisSequenceResolver($this->redis);
            } catch (\Exception) {
                // Redis 连接不可用时静默回退到随机序列解析器
                // 在开发和测试环境中，这是正常行为
            }
        }

        return new RandomSequenceResolver();
    }
}
