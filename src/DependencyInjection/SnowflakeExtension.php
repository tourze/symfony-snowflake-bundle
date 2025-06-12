<?php

namespace Tourze\SnowflakeBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class SnowflakeExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $this->prependRedis($container);
    }

    /**
     * Redis配置
     */
    private function prependRedis(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('snc_redis', [
            'clients' => [
                'snowflake' => [
                    'type' => 'phpredis',
                    'alias' => 'lock',
                    'dsn' => $_ENV['SNOWFLAKE_REDIS_URL'] ?? $_ENV['REDIS_URL'] ?? 'redis://127.0.0.1:6379',
                    'logging' => false,
                ],
            ],
        ]);
    }
}
