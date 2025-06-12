<?php

namespace Tourze\SnowflakeBundle\Service;

use Godruoyi\Snowflake\RandomSequenceResolver;
use Godruoyi\Snowflake\RedisSequenceResolver;
use Godruoyi\Snowflake\SequenceResolver;
use Redis;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ResolverFactory
{
    public function __construct(
        #[Autowire(service: 'snc_redis.snowflake')] private readonly ?Redis $redis = null,
    )
    {
    }

    public function resolver(): SequenceResolver
    {
        if ($this->redis !== null) {
            return new RedisSequenceResolver($this->redis);
        }
        return new RandomSequenceResolver();
    }
}
