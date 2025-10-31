<?php

declare(strict_types=1);

namespace Tourze\SnowflakeBundle\Service;

use Godruoyi\Snowflake\SequenceResolver;

/**
 * SequenceResolver 工厂接口
 */
interface ResolverFactoryInterface
{
    /**
     * 创建序列解析器
     *
     * @return SequenceResolver 返回适当的序列解析器实例
     */
    public function resolver(): SequenceResolver;
}
