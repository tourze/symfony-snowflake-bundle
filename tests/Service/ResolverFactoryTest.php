<?php

namespace Tourze\SnowflakeBundle\Tests\Service;

use Godruoyi\Snowflake\RandomSequenceResolver;
use Godruoyi\Snowflake\SequenceResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\SnowflakeBundle\Service\ResolverFactory;

/**
 * @internal
 */
#[CoversClass(ResolverFactory::class)]
#[RunTestsInSeparateProcesses]
final class ResolverFactoryTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
    }

    /**
     * 测试在没有Redis的情况下返回RandomSequenceResolver
     */
    public function testResolverWithoutRedisReturnsRandomSequenceResolver(): void
    {
        $resolverFactory = self::getService(ResolverFactory::class);
        $resolver = $resolverFactory->resolver();

        $this->assertInstanceOf(RandomSequenceResolver::class, $resolver);
    }

    /**
     * 测试构造函数正确处理可选的Redis依赖
     */
    public function testConstructorHandlesOptionalRedisDependency(): void
    {
        // 测试容器中的服务实例化
        $resolverFactory = self::getService(ResolverFactory::class);
        $this->assertInstanceOf(ResolverFactory::class, $resolverFactory);
    }

    /**
     * 测试 resolver 方法返回的对象实现了 SequenceResolver 接口
     */
    public function testResolverReturnsSequenceResolverInterface(): void
    {
        $resolverFactory = self::getService(ResolverFactory::class);
        $resolver = $resolverFactory->resolver();
        $this->assertInstanceOf(SequenceResolver::class, $resolver);
    }

    /**
     * 测试从容器获取的服务基本功能
     */
    public function testResolverServiceBasicFunctionality(): void
    {
        $resolverFactory = self::getService(ResolverFactory::class);
        $this->assertInstanceOf(ResolverFactory::class, $resolverFactory);

        $resolver = $resolverFactory->resolver();
        $this->assertInstanceOf(SequenceResolver::class, $resolver);
    }
}
