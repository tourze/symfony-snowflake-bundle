# Symfony Snowflake Bundle

[![Packagist](https://img.shields.io/packagist/v/tourze/symfony-snowflake-bundle.svg)](https://packagist.org/packages/tourze/symfony-snowflake-bundle)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

## Introduction

A high-performance, distributed Snowflake ID generator bundle for Symfony, supporting Redis-based sequence distribution. Designed for scenarios requiring globally unique and high-concurrency IDs.

## Features

- Powered by [godruoyi/php-snowflake](https://github.com/godruoyi/php-snowflake) for 64-bit unique ID generation
- Supports Redis sequence resolver for enhanced uniqueness under high concurrency
- Auto-generates WorkerId based on hostname, zero configuration required
- Compatible with Symfony 6.4/7.1, supports autowiring
- Easy-to-use service interface for seamless integration

## Installation

### Requirements
- PHP >= 8.1
- Symfony >= 6.4
- Redis (recommended for distributed sequence safety)

### Install via Composer

```bash
composer require tourze/symfony-snowflake-bundle
```

## Quick Start

### 1. Register the Bundle (if not auto-discovered)

Add to `config/bundles.php`:

```php
return [
    // ...
    Tourze\SnowflakeBundle\SnowflakeBundle::class => ['all' => true],
];
```

### 2. Generate a Snowflake ID

Inject `Tourze\SnowflakeBundle\Service\Snowflake` into your service or controller, then call `id()`:

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

## Usage

### Service Injection

Type-hint `Snowflake` for autowiring.

### Generate Unique ID

```php
$id = $snowflake->id();
```

### WorkerId Generation

Automatically derived from the hostname to avoid conflicts in multi-instance deployments.

### Redis Distributed Sequence

If `snc/redis-bundle` is configured, Redis will be used for sequence distribution to ensure uniqueness under high concurrency.

## Configuration

No extra configuration is required for most use cases. For advanced customization, extend `ResolverFactory`.

## Best Practices & Caveats

- It is recommended to enable Redis in production for maximum uniqueness
- For large-scale distributed deployments, review WorkerId generation to avoid rare conflicts

## Contributing

Issues and PRs are welcome. See [GitHub Project](https://github.com/tourze/symfony-snowflake-bundle)

## License

This project is licensed under the MIT License. See [LICENSE](LICENSE)
