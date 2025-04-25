# Symfony Snowflake Bundle

[![Packagist](https://img.shields.io/packagist/v/tourze/symfony-snowflake-bundle.svg)](https://packagist.org/packages/tourze/symfony-snowflake-bundle)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

## Introduction

A high-performance, distributed Snowflake ID generator bundle for Symfony applications. This bundle implements Twitter's Snowflake algorithm to generate unique, time-ordered, 64-bit IDs for distributed systems. It is designed for scenarios requiring globally unique IDs under high concurrency.

## Features

- Generates 64-bit unique IDs based on [godruoyi/php-snowflake](https://github.com/godruoyi/php-snowflake) library
- Built-in Redis sequence resolver to ensure uniqueness in high-concurrency environments
- Auto-generates WorkerId based on hostname for distributed scenario support
- Zero configuration required for basic usage
- Fully compatible with Symfony 6.4/7.1+
- Autowiring support for easy integration with Symfony services
- Thread-safe ID generation
- Time-ordered IDs for efficient database indexing

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

Inject the Snowflake service into your service or controller:

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
        // Generate a unique ID for your new product
        $uniqueId = $this->snowflake->id();
        
        // Use the ID in your application logic
        return $uniqueId;
    }
}
```

## Usage

### Basic Usage

Simply inject the Snowflake service and call the `id()` method:

```php
// Inject via constructor
public function __construct(private readonly Snowflake $snowflake) {}

// Generate a unique ID
$id = $this->snowflake->id();
```

### ID Format and Structure

The generated ID is a 64-bit integer with the following structure:

- 41 bits for timestamp (milliseconds since the epoch or custom epoch)
- 10 bits for worker ID (machine ID)
- 12 bits for sequence number (per millisecond counter)

This structure allows for:
- ~69 years of unique timestamps from custom epoch
- 1024 different worker IDs
- 4096 IDs per millisecond per worker

### WorkerId Generation

By default, the WorkerId is automatically derived from the hostname using a CRC32 hash modulo operation:

```php
$workerId = crc32(gethostname()) % 32; // Returns a value between 0-31
```

This ensures different server instances generally get different WorkerIds without manual configuration.

### Redis-Based Sequence Resolver

When `snc/redis-bundle` is installed and configured in your application, the Snowflake bundle automatically uses Redis for sequence distribution, which provides:

- Enhanced uniqueness guarantees under high concurrency
- Improved resistance to clock drift
- Better distribution of IDs across multiple instances

## Configuration

For basic usage, no extra configuration is required. The bundle works with sensible defaults.

### Advanced Configuration

For more advanced scenarios, you may extend the `ResolverFactory` to provide a custom sequence resolver:

```php
<?php

namespace App\Service;

use Godruoyi\Snowflake\SequenceResolver;
use Tourze\SnowflakeBundle\Service\ResolverFactory;

class CustomResolverFactory extends ResolverFactory
{
    public function resolver(): SequenceResolver
    {
        // Your custom resolver implementation
        return new YourCustomSequenceResolver();
    }
}
```

Then register your custom factory in your service configuration.

## Best Practices

- **Redis in Production**: Always use Redis in production environments for sequence distribution
- **Clock Synchronization**: Ensure your server clocks are synchronized with NTP
- **Worker ID Management**: For large distributed deployments, consider implementing a centralized WorkerId assignment mechanism
- **ID Storage**: Store Snowflake IDs as `BIGINT` in databases (or strings if your DB doesn't support 64-bit integers)
- **Benchmarking**: Test performance in your environment as high throughput may require tuning

## Potential Pitfalls

- **Clock Moving Backwards**: If server time moves backward due to NTP adjustments, duplicate IDs might be generated
- **Worker ID Conflicts**: In very large deployments, hostname-based WorkerId generation might lead to conflicts
- **Performance without Redis**: Without Redis, high concurrency might lead to duplicates under extreme circumstances

## Contributing

Issues and pull requests are welcome! Please visit the [GitHub repository](https://github.com/tourze/symfony-snowflake-bundle) to contribute.

## License

This bundle is available under the MIT License. See the [LICENSE](LICENSE) file for more information.
