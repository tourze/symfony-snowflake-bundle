# Snowflake 雪花 ID 生成器 Symfony Bundle

[![Packagist](https://img.shields.io/packagist/v/tourze/symfony-snowflake-bundle.svg)](https://packagist.org/packages/tourze/symfony-snowflake-bundle)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

## 项目简介

本模块为 Symfony 提供高性能、分布式的雪花（Snowflake）ID 生成服务，支持 Redis 分布式序列分配，适用于需要全局唯一、高并发 ID 的业务场景。

## 功能特性

- 基于 [godruoyi/php-snowflake](https://github.com/godruoyi/php-snowflake) 实现，生成 64 位全局唯一 ID
- 支持 Redis 分布式序列分配，提升高并发下的唯一性
- 自动根据主机名生成 WorkerId，零配置即可使用
- 完全兼容 Symfony 6.4/7.1，支持自动注入
- 提供简单易用的服务接口，方便集成

## 安装方法

### 依赖要求

- PHP >= 8.1
- Symfony >= 6.4
- 推荐安装并配置 Redis（用于分布式序列号）

### 安装命令

```bash
composer require tourze/symfony-snowflake-bundle
```

## 快速上手

### 1. 注册 Bundle（如未自动发现）

在 `config/bundles.php` 添加：

```php
return [
    // ...
    Tourze\SnowflakeBundle\SnowflakeBundle::class => ['all' => true],
];
```

### 2. 获取雪花 ID

在任意服务或控制器中注入 `Tourze\SnowflakeBundle\Service\Snowflake`，直接调用 `id()` 方法获取唯一 ID：

```php
use Tourze\SnowflakeBundle\Service\Snowflake;

class DemoService
{
    public function __construct(private Snowflake $snowflake) {}

    public function create(): string
    {
        return $this->snowflake->id();
    }
}
```

## 使用说明

### 服务注入

通过类型提示自动注入 `Snowflake` 服务。

### 获取唯一 ID

```php
$id = $snowflake->id();
```

### WorkerId 生成逻辑

自动基于主机名生成，避免多实例部署时冲突。

### Redis 分布式序列

如项目已配置 `snc/redis-bundle`，将自动使用 Redis 分配序列号，提升高并发安全性。

## 配置说明

通常无需额外配置。如需自定义，可扩展 `ResolverFactory`。

## 最佳实践与注意事项

- 推荐生产环境部署 Redis，保障高并发下唯一性
- 若大规模分布式部署，建议关注 WorkerId 生成策略，避免极端场景下冲突

## 贡献指南

欢迎 Issue 与 PR，详见 [GitHub 项目](https://github.com/tourze/symfony-snowflake-bundle)

## License

本项目基于 MIT 协议开源，详见 [LICENSE](LICENSE)
