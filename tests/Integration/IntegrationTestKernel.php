<?php

namespace Tourze\SnowflakeBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Tourze\SnowflakeBundle\Service\ResolverFactory;
use Tourze\SnowflakeBundle\Service\Snowflake;
use Tourze\SnowflakeBundle\SnowflakeBundle;

class IntegrationTestKernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new SnowflakeBundle();
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        // 基本框架配置
        $container->extension('framework', [
            'secret' => 'TEST_SECRET',
            'test' => true,
            'http_method_override' => false,
            'handle_all_throwables' => true,
            'php_errors' => [
                'log' => true,
            ],
        ]);

        // 手动注册服务
        $services = $container->services();
        $services->set(ResolverFactory::class)
            ->autowire()
            ->autoconfigure();

        $services->set(Snowflake::class)
            ->autowire()
            ->autoconfigure()
            ->public();
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/var/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/var/log';
    }
}
