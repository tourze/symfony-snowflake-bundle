<?php

namespace Tourze\SnowflakeBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\SnowflakeBundle\DependencyInjection\SnowflakeExtension;

class SnowflakeExtensionTest extends TestCase
{
    public function test_load_registersExpectedServices(): void
    {
        $container = new ContainerBuilder();
        $extension = new SnowflakeExtension();

        $extension->load([], $container);

        // 验证主要服务是否已注册
        $this->assertTrue($container->hasDefinition('Tourze\SnowflakeBundle\Service\ResolverFactory'));
        $this->assertTrue($container->hasDefinition('Tourze\SnowflakeBundle\Service\Snowflake'));
    }

    public function test_implements_extensionInterface(): void
    {
        $extension = new SnowflakeExtension();

        $this->assertInstanceOf(\Symfony\Component\DependencyInjection\Extension\ExtensionInterface::class, $extension);
    }

    public function test_getAlias_returnsSnowflake(): void
    {
        $extension = new SnowflakeExtension();

        $this->assertEquals('snowflake', $extension->getAlias());
    }
}
