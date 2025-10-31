<?php

namespace Tourze\SnowflakeBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use Tourze\SnowflakeBundle\DependencyInjection\SnowflakeExtension;

/**
 * @internal
 */
#[CoversClass(SnowflakeExtension::class)]
final class SnowflakeExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testLoadRegistersExpectedServices(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');
        $extension = new SnowflakeExtension();

        $extension->load([], $container);

        // 验证主要服务是否已注册
        $this->assertTrue($container->hasDefinition('Tourze\SnowflakeBundle\Service\ResolverFactory'));
        $this->assertTrue($container->hasDefinition('Tourze\SnowflakeBundle\Service\Snowflake'));
    }

    public function testImplementsExtensionInterface(): void
    {
        $extension = new SnowflakeExtension();

        $this->assertInstanceOf(ExtensionInterface::class, $extension);
    }

    public function testGetAliasReturnsSnowflake(): void
    {
        $extension = new SnowflakeExtension();

        $this->assertEquals('snowflake', $extension->getAlias());
    }
}
