<?php

namespace Tourze\SnowflakeBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class SnowflakeExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');

        $factoryDef = new Reference('snc_redis.phpredis_factory');
        $redisDef = new Definition(\Redis::class);
        $redisDef->setFactory([$factoryDef, 'create']);
        $redisDef->setArguments([
            \Redis::class,
            [
                $_ENV['SNOWFLAKE_REDIS_URL'] ?? $_ENV['REDIS_URL'] ?? 'redis://127.0.0.1:6379',
            ],
            [
                'connection_timeout' => 5,
            ],
            'snowflake',
            false,
        ]);
        $container->setDefinition('snc_redis.snowflake', $redisDef);
    }
}
