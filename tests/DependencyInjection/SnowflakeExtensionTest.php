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

    public function test_implements_prependExtensionInterface(): void
    {
        $extension = new SnowflakeExtension();

        $this->assertInstanceOf(\Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface::class, $extension);
    }

    public function test_prepend_configuresRedisClient(): void
    {
        $container = new ContainerBuilder();
        $extension = new SnowflakeExtension();

        $extension->prepend($container);

        // 验证 Redis 配置是否被正确预处理
        $configs = $container->getExtensionConfig('snc_redis');
        $this->assertCount(1, $configs);

        $config = $configs[0];
        $this->assertArrayHasKey('clients', $config);
        $this->assertArrayHasKey('snowflake', $config['clients']);

        $snowflakeClient = $config['clients']['snowflake'];
        $this->assertEquals('phpredis', $snowflakeClient['type']);
        $this->assertEquals('lock', $snowflakeClient['alias']);
        $this->assertFalse($snowflakeClient['logging']);
        $this->assertIsString($snowflakeClient['dsn']);
    }

    public function test_prepend_usesEnvironmentVariables(): void
    {
        // 测试环境变量的使用
        $originalRedisUrl = $_ENV['REDIS_URL'] ?? null;
        $originalSnowflakeRedisUrl = $_ENV['SNOWFLAKE_REDIS_URL'] ?? null;

        try {
            // 设置测试环境变量
            $_ENV['SNOWFLAKE_REDIS_URL'] = 'redis://test-snowflake:6379';

            $container = new ContainerBuilder();
            $extension = new SnowflakeExtension();

            $extension->prepend($container);

            $configs = $container->getExtensionConfig('snc_redis');
            $config = $configs[0];
            $snowflakeClient = $config['clients']['snowflake'];

            $this->assertEquals('redis://test-snowflake:6379', $snowflakeClient['dsn']);

            // 清除 SNOWFLAKE_REDIS_URL，测试回退到 REDIS_URL
            unset($_ENV['SNOWFLAKE_REDIS_URL']);
            $_ENV['REDIS_URL'] = 'redis://test-redis:6379';

            $container2 = new ContainerBuilder();
            $extension->prepend($container2);

            $configs2 = $container2->getExtensionConfig('snc_redis');
            $config2 = $configs2[0];
            $snowflakeClient2 = $config2['clients']['snowflake'];

            $this->assertEquals('redis://test-redis:6379', $snowflakeClient2['dsn']);
        } finally {
            // 恢复原始环境变量
            if ($originalRedisUrl !== null) {
                $_ENV['REDIS_URL'] = $originalRedisUrl;
            } else {
                unset($_ENV['REDIS_URL']);
            }

            if ($originalSnowflakeRedisUrl !== null) {
                $_ENV['SNOWFLAKE_REDIS_URL'] = $originalSnowflakeRedisUrl;
            } else {
                unset($_ENV['SNOWFLAKE_REDIS_URL']);
            }
        }
    }
}
