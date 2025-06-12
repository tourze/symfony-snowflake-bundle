<?php

namespace Tourze\SnowflakeBundle;

use Snc\RedisBundle\SncRedisBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;

class SnowflakeBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            SncRedisBundle::class => ['all' => true],
        ];
    }
}
