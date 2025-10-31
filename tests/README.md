# Symfony Snowflake Bundle 测试文档

## 运行测试

在项目根目录执行：

```bash
./vendor/bin/phpunit packages/symfony-snowflake-bundle/tests
```

## 测试覆盖范围

### 单元测试

- **ResolverFactory** (2个测试用例)
  - 无Redis情况下使用随机序列分配器
  - 有Redis情况下使用Redis序列分配器
- **Snowflake** (10个测试用例)
  - WorkerId生成逻辑（多种场景数据驱动测试）
  - 生成器缓存机制
  - ID生成和唯一性验证
- **SnowflakeBundle** (2个测试用例)
  - Bundle基础功能和接口实现
  - 路径返回正确性
- **SnowflakeExtension** (3个测试用例)
  - 服务注册验证
  - Extension接口实现
  - 别名配置

### 集成测试 (4个测试用例)

- 服务在Symfony容器中的注册与装配
- 雪花ID生成功能验证
- 批量ID唯一性验证
- 容器服务可用性检查

## 贡献测试

当向包添加新功能时，请确保：

1. 为新功能编写单元测试
2. 更新集成测试以覆盖新功能
3. 所有现有测试仍然通过
4. 测试覆盖率保持在高水平
