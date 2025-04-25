# Symfony Snowflake 雪花 ID 生成器

[![Packagist](https://img.shields.io/packagist/v/tourze/symfony-snowflake-bundle.svg)](https://packagist.org/packages/tourze/symfony-snowflake-bundle)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

## 项目简介

为 Symfony 应用提供高性能、分布式的雪花（Snowflake）ID 生成服务。本模块实现了 Twitter 的雪花算法，用于生成有序、唯一的 64 位 ID，特别适用于分布式系统和需要全局唯一 ID 的高并发场景。

## 功能特性

- 基于 [godruoyi/php-snowflake](https://github.com/godruoyi/php-snowflake) 库实现，生成 64 位全局唯一 ID
- 内置 Redis 序列分配器，确保高并发环境下的唯一性
- 自动基于主机名生成 WorkerId，支持分布式部署场景
- 零配置即可使用，简单直观
- 完全兼容 Symfony 6.4/7.1+
- 支持 Symfony 自动注入，便于与服务集成
- 线程安全的 ID 生成机制
- 生成的 ID 具有时间顺序性，利于数据库索引优化

## 安装方法

### 环境要求

- PHP >= 8.1
- Symfony >= 6.4
- Redis（推荐用于分布式序列安全）

### 通过 Composer 安装

```bash
composer require tourze/symfony-snowflake-bundle
```

## 快速上手

### 1. 注册 Bundle（如未自动发现）

在 `config/bundles.php` 中添加：

```php
return [
    // ...
    Tourze\SnowflakeBundle\SnowflakeBundle::class => ['all' => true],
];
```

### 2. 生成雪花 ID

在服务或控制器中注入 Snowflake 服务：

```php
<?php

namespace App\Service;

use Tourze\SnowflakeBundle\Service\Snowflake;

class ProductService
{
    public function __construct(
        private readonly Snowflake $snowflake
    ) {
    }

    public function createProduct(): int|string
    {
        // 为新产品生成唯一ID
        $uniqueId = $this->snowflake->id();
        
        // 在业务逻辑中使用该ID
        return $uniqueId;
    }
}
```

## 使用说明

### 基本用法

通过依赖注入引入 Snowflake 服务，然后调用 `id()` 方法：

```php
// 通过构造函数注入
public function __construct(private readonly Snowflake $snowflake) {}

// 生成唯一ID
$id = $this->snowflake->id();
```

### ID 格式与结构

生成的 ID 是一个 64 位整数，结构如下：

- 41 位时间戳（毫秒级，从纪元或自定义纪元开始）
- 10 位工作机器 ID（机器 ID）
- 12 位序列号（每毫秒内的计数器）

这种结构允许：
- 从自定义纪元起约 69 年的唯一时间戳
- 1024 个不同的工作机器 ID
- 每个工作机器每毫秒可生成 4096 个 ID

### WorkerId 生成逻辑

默认情况下，WorkerId 通过主机名的 CRC32 哈希取模运算自动生成：

```php
$workerId = crc32(gethostname()) % 32; // 返回 0-31 之间的值
```

这确保不同服务器实例通常能获得不同的 WorkerId，无需手动配置。

### 基于 Redis 的序列分配器

当应用中安装并配置了 `snc/redis-bundle` 后，Snowflake 模块会自动使用 Redis 进行序列分配，提供：

- 高并发环境下增强的唯一性保证
- 更好地抵抗时钟漂移
- 在多实例间更均匀地分配 ID

## 配置说明

基本使用场景下无需额外配置，模块采用合理的默认设置。

### 高级配置

对于更高级的场景，可以扩展 `ResolverFactory` 来提供自定义序列分配器：

```php
<?php

namespace App\Service;

use Godruoyi\Snowflake\SequenceResolver;
use Tourze\SnowflakeBundle\Service\ResolverFactory;

class CustomResolverFactory extends ResolverFactory
{
    public function resolver(): SequenceResolver
    {
        // 自定义序列分配器实现
        return new YourCustomSequenceResolver();
    }
}
```

然后在服务配置中注册自定义工厂。

## 最佳实践

- **生产环境使用 Redis**：始终在生产环境中使用 Redis 进行序列分配
- **时钟同步**：确保服务器时钟通过 NTP 保持同步
- **WorkerId 管理**：对于大型分布式部署，考虑实现集中式 WorkerId 分配机制
- **ID 存储**：在数据库中使用 `BIGINT` 类型存储雪花 ID（如果数据库不支持 64 位整数，则使用字符串）
- **性能测试**：在您的环境中进行性能测试，高吞吐量可能需要调优

## 注意事项

- **时钟回拨问题**：如果服务器时间因 NTP 调整而回拨，可能会生成重复 ID
- **WorkerId 冲突**：在非常大的部署中，基于主机名的 WorkerId 生成可能导致冲突
- **无 Redis 时的性能**：没有 Redis 的情况下，极端高并发可能导致重复

## 贡献指南

欢迎提交 Issue 和 Pull Request！请访问 [GitHub 仓库](https://github.com/tourze/symfony-snowflake-bundle) 参与贡献。

## 许可协议

本模块基于 MIT 许可协议开源。详情请参阅 [LICENSE](LICENSE) 文件。
