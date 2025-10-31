<?php

namespace Tourze\SnowflakeBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\RedisDedicatedConnectionBundle\RedisDedicatedConnectionBundle;

class SnowflakeBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            RedisDedicatedConnectionBundle::class => ['all' => true],
        ];
    }
}
