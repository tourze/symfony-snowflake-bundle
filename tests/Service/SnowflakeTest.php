<?php

namespace Tourze\SnowflakeBundle\Tests\Service;

use Godruoyi\Snowflake\RandomSequenceResolver;
use Godruoyi\Snowflake\SequenceResolver;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tourze\SnowflakeBundle\Service\ResolverFactory;
use Tourze\SnowflakeBundle\Service\Snowflake;

class SnowflakeTest extends TestCase
{
    /**
     * 在每个测试前重置静态属性
     */
    protected function setUp(): void
    {
        $reflectionClass = new ReflectionClass(Snowflake::class);
        $reflectionProperty = $reflectionClass->getProperty('generators');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null, []);
    }

    /**
     * 测试构造函数是否正确创建Snowflake实例
     */
    public function testConstructor(): void
    {
        $resolverFactory = $this->createMock(ResolverFactory::class);
        $sequenceResolver = $this->createMock(SequenceResolver::class);

        $resolverFactory->method('resolver')
            ->willReturn($sequenceResolver);

        $snowflake = new Snowflake($resolverFactory);

        $this->assertInstanceOf(Snowflake::class, $snowflake);
    }

    /**
     * 测试generateWorkerId方法在不同情况下的行为
     *
     * @dataProvider workerIdDataProvider
     */
    public function testGenerateWorkerId(string $hostname, int $maxWorkerId, int $expectedInRange): void
    {
        $workerId = Snowflake::generateWorkerId($hostname, $maxWorkerId);

        // 验证workerId在预期范围内
        $this->assertGreaterThanOrEqual(0, $workerId);
        $this->assertLessThanOrEqual($expectedInRange, $workerId);
    }

    /**
     * 为testGenerateWorkerId提供测试数据
     */
    public function workerIdDataProvider(): array
    {
        return [
            'default_max_worker_id' => ['localhost', 31, 31],
            'custom_max_worker_id' => ['localhost', 63, 63],
            'zero_max_worker_id' => ['localhost', 0, 0],
            'empty_hostname' => ['', 31, 31],
            'special_characters_hostname' => ['server!@#$%^&*()', 31, 31],
            'long_hostname' => [str_repeat('a', 1000), 31, 31],
        ];
    }

    /**
     * 测试getGenerator方法的缓存机制
     */
    public function testGetGenerator(): void
    {
        $generator1 = Snowflake::getGenerator(1, 1);
        $generator2 = Snowflake::getGenerator(1, 1);
        $generator3 = Snowflake::getGenerator(2, 2);

        // 相同参数应该返回相同实例
        $this->assertSame($generator1, $generator2);

        // 不同参数应该返回不同实例
        $this->assertNotSame($generator1, $generator3);
    }

    /**
     * 测试id方法返回有效的雪花ID
     */
    public function testId(): void
    {
        $resolverFactory = $this->createMock(ResolverFactory::class);
        $resolverFactory->method('resolver')
            ->willReturn(new RandomSequenceResolver());

        $snowflake = new Snowflake($resolverFactory);
        $id = $snowflake->id();

        // 验证ID是一个非空字符串
        $this->assertIsString($id);
        $this->assertNotEmpty($id);

        // 验证ID是一个数字字符串
        $this->assertIsNumeric($id);

        // 验证ID的长度（应该至少有10位数）
        $this->assertGreaterThan(10, strlen($id));
    }

    /**
     * 测试在短时间内多次调用id方法返回唯一ID
     */
    public function testIdUniqueness(): void
    {
        $resolverFactory = $this->createMock(ResolverFactory::class);
        $resolverFactory->method('resolver')
            ->willReturn(new RandomSequenceResolver());

        $snowflake = new Snowflake($resolverFactory);

        $ids = [];
        $count = 100; // 生成100个ID

        for ($i = 0; $i < $count; $i++) {
            $ids[] = $snowflake->id();
        }

        // 验证生成的所有ID都是唯一的
        $uniqueIds = array_unique($ids);
        $this->assertCount($count, $uniqueIds);

        // 验证ID是按顺序生成的（每个ID应该大于前一个）
        for ($i = 1; $i < $count; $i++) {
            $this->assertGreaterThan($ids[$i - 1], $ids[$i]);
        }
    }
}
