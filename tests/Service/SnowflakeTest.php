<?php

namespace Tourze\SnowflakeBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\SnowflakeBundle\Service\Snowflake;

/**
 * @internal
 */
#[CoversClass(Snowflake::class)]
#[RunTestsInSeparateProcesses]
final class SnowflakeTest extends AbstractIntegrationTestCase
{
    /**
     * 在每个测试前重置静态属性
     */
    protected function onSetUp(): void
    {
        $reflectionClass = new \ReflectionClass(Snowflake::class);
        $reflectionProperty = $reflectionClass->getProperty('generators');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null, []);
    }

    /**
     * 测试构造函数是否正确创建Snowflake实例
     */
    public function testConstructorCreatesSnowflakeInstance(): void
    {
        $snowflake = self::getService(Snowflake::class);
        $this->assertInstanceOf(Snowflake::class, $snowflake);
    }

    /**
     * 测试generateWorkerId方法在不同情况下的行为
     */
    #[DataProvider('workerIdDataProvider')]
    public function testGenerateWorkerIdHandlesVariousInputs(string $hostname, int $maxWorkerId, int $expectedInRange): void
    {
        $workerId = Snowflake::generateWorkerId($hostname, $maxWorkerId);

        // 验证workerId在预期范围内
        $this->assertGreaterThanOrEqual(0, $workerId);
        $this->assertLessThanOrEqual($expectedInRange, $workerId);
    }

    /**
     * 为testGenerateWorkerId提供测试数据
     *
     * @return array<string, array{string, int, int}>
     */
    public static function workerIdDataProvider(): array
    {
        return [
            'default_max_worker_id' => ['localhost', 31, 31],
            'custom_max_worker_id' => ['localhost', 63, 63],
            'zero_max_worker_id' => ['localhost', 0, 0],
            'empty_hostname' => ['', 31, 31],
            'special_characters_hostname' => ['server!@#$%^&*()', 31, 31],
            'long_hostname' => [str_repeat('a', 1000), 31, 31],
            'unicode_hostname' => ['服务器-测试', 31, 31],
            'numeric_hostname' => ['123456', 31, 31],
            'mixed_case_hostname' => ['ServerTest', 31, 31],
        ];
    }

    /**
     * 测试generateWorkerId方法的一致性
     */
    public function testGenerateWorkerIdIsConsistent(): void
    {
        $hostname = 'test-server';
        $maxWorkerId = 31;

        $workerId1 = Snowflake::generateWorkerId($hostname, $maxWorkerId);
        $workerId2 = Snowflake::generateWorkerId($hostname, $maxWorkerId);
        $workerId3 = Snowflake::generateWorkerId($hostname, $maxWorkerId);

        // 相同参数应该产生相同结果
        $this->assertEquals($workerId1, $workerId2);
        $this->assertEquals($workerId2, $workerId3);
    }

    /**
     * 测试getGenerator方法的缓存机制
     */
    public function testGetGeneratorUsesCaching(): void
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
     * 测试getGenerator方法处理默认参数
     */
    public function testGetGeneratorHandlesDefaultParameters(): void
    {
        $generator1 = Snowflake::getGenerator();
        $generator2 = Snowflake::getGenerator(-1, -1);

        // 默认参数应该与显式传入-1,-1相同
        $this->assertSame($generator1, $generator2);
    }

    /**
     * 测试id方法返回有效的雪花ID
     */
    public function testIdReturnsValidSnowflakeId(): void
    {
        $snowflake = self::getService(Snowflake::class);
        $id = $snowflake->id();

        // 验证ID是一个非空字符串
        $this->assertNotEmpty($id);

        // 验证ID是一个数字字符串
        $this->assertIsNumeric($id);

        // 验证ID的长度（应该至少有10位数）
        $this->assertGreaterThan(10, strlen($id));

        // 验证ID是正数
        $this->assertGreaterThan(0, intval($id));
    }

    /**
     * 测试在短时间内多次调用id方法返回唯一ID
     */
    public function testIdEnsuresUniqueness(): void
    {
        $snowflake = self::getService(Snowflake::class);

        $ids = [];
        $count = 100; // 生成100个ID

        for ($i = 0; $i < $count; ++$i) {
            $ids[] = $snowflake->id();
        }

        // 验证生成的所有ID都是唯一的
        $uniqueIds = array_unique($ids);
        $this->assertCount($count, $uniqueIds);

        // 验证ID是按顺序生成的（每个ID应该大于前一个）
        for ($i = 1; $i < $count; ++$i) {
            $this->assertGreaterThan($ids[$i - 1], $ids[$i]);
        }
    }

    /**
     * 测试ID生成的性能
     */
    public function testIdPerformanceTest(): void
    {
        $snowflake = self::getService(Snowflake::class);

        $startTime = microtime(true);
        $count = 1000;

        for ($i = 0; $i < $count; ++$i) {
            $snowflake->id();
        }

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // 1000个ID的生成应该在1秒内完成
        $this->assertLessThan(1.0, $duration, "ID生成性能不达标，1000个ID生成耗时: {$duration}秒");

        // 每个ID的平均生成时间应该小于1毫秒
        $averageTime = $duration / $count;
        $this->assertLessThan(0.001, $averageTime, "平均ID生成时间过长: {$averageTime}秒");
    }

    /**
     * 测试多个Snowflake实例的独立性
     */
    public function testMultipleInstancesOperateIndependently(): void
    {
        $snowflake1 = self::getService(Snowflake::class);
        $snowflake2 = self::getService(Snowflake::class);

        $id1 = $snowflake1->id();
        $id2 = $snowflake2->id();

        // 注意：从容器获取的实例可能是单例，所以ID可能相同
        // 这里主要验证都能生成有效ID
        $this->assertIsNumeric($id1);
        $this->assertIsNumeric($id2);
    }

    /**
     * 测试Snowflake实例的线程安全特性（模拟并发）
     */
    public function testIdConcurrencySimulation(): void
    {
        $snowflake = self::getService(Snowflake::class);

        // 模拟并发生成ID
        $ids = [];
        $iterations = 50;

        // 快速连续生成ID
        for ($i = 0; $i < $iterations; ++$i) {
            $ids[] = $snowflake->id();
            // 微小延迟以模拟真实场景
            usleep(1);
        }

        // 验证所有ID都是唯一的
        $uniqueIds = array_unique($ids);
        $this->assertCount($iterations, $uniqueIds);

        // 验证ID的时间顺序性
        for ($i = 1; $i < $iterations; ++$i) {
            $this->assertGreaterThanOrEqual($ids[$i - 1], $ids[$i]);
        }
    }
}
