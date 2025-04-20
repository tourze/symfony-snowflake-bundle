<?php

namespace Tourze\SnowflakeBundle\Service;

use Godruoyi\Snowflake\RandomSequenceResolver;
use Godruoyi\Snowflake\SequenceResolver;
use Godruoyi\Snowflake\Snowflake as BaseSnowflake;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * 雪花算法发号器
 *
 * 雪花ID生成器，因为我们目标只是为了生成一个尽可能随机的数值，所以下面没传入数据中心和机器ID，如果有需要可以自定义传入
 * 为了减少高并发下的id重复问题，我们在这里用了redis来做序列号的分发
 *
 * 这里，我们还要看看怎么改造成redis来发号，因为理论上，同一个时刻还是有 1/961 概率会重复。
 * 但是因为这个发号器是多个表都共用的，所以理论上重复的概率还会更加低
 */
#[Autoconfigure(lazy: true, public: true, autowire: true)]
class Snowflake
{
    /**
     * @var array|BaseSnowflake[]
     */
    private static array $generators = [];

    public static function getGenerator(int $datacenter = -1, int $workerId = -1, ?SequenceResolver $resolver = null): BaseSnowflake
    {
        $key = "{$datacenter}-{$workerId}";
        if (!isset(static::$generators[$key])) {
            $generator = new BaseSnowflake(
                $datacenter,
                $workerId,
            );
            $generator->setSequenceResolver($resolver ?: new RandomSequenceResolver());
            static::$generators[$key] = $generator;
        }
        return static::$generators[$key];
    }

    private BaseSnowflake $generator;

    public function __construct(private readonly ResolverFactory $resolverFactory)
    {
        $this->generator = static::getGenerator(
            -1,
            self::generateWorkerId(gethostname()),
            $this->resolverFactory->resolver(),
        );
    }

    /**
     * 生成基于主机名的机器ID
     *
     * @param string $hostname 主机名
     * @param int $maxWorkerId 最大机器ID，通常是机器ID的位数对应的最大值
     * @return int 生成的机器ID
     */
    public static function generateWorkerId(string $hostname, int $maxWorkerId = 31): int
    {
        // 将主机名转换为一个哈希值
        $hash = crc32($hostname);

        // 将哈希值限制在机器ID的范围内
        return $hash % ($maxWorkerId + 1);
    }

    public function id(): string
    {
        return $this->generator->id();
    }
}
