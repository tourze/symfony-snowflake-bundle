<?php

namespace Tourze\SnowflakeBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class SnowflakeExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
