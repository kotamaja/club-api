<?php

namespace App\Tests\Unit\Mapper;

use App\Mapper\CustomMapperInterface;
use App\Mapper\Exception\MappingException;
use App\Mapper\MapperRegistry;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class MapperRegistryTest extends TestCase
{
    public function testMapCreatesTargetFromClassString(): void
    {
        $source = new SourceStub('hello');

        $mapper = new class implements CustomMapperInterface {
            public function map(mixed $source, mixed $target = null): mixed
            {
                \assert($source instanceof SourceStub);
                \assert($target instanceof TargetStub);

                $target->value = $source->value;

                return $target;
            }
        };

        $locator = $this->createMock(ContainerInterface::class);
        $locator
            ->expects($this->once())
            ->method('get')
            ->with('mapper.source_to_target')
            ->willReturn($mapper);

        $registry = new MapperRegistry(
            mapperIdsByKey: [
                SourceStub::class . '->' . TargetStub::class => 'mapper.source_to_target',
            ],
            mapperLocator: $locator,
        );

        $result = $registry->map($source, TargetStub::class);

        $this->assertInstanceOf(TargetStub::class, $result);
        $this->assertSame('hello', $result->value);
    }

    public function testMapMutatesExistingTargetObject(): void
    {
        $source = new SourceStub('updated');
        $target = new TargetStub();
        $target->value = 'before';

        $mapper = new class implements CustomMapperInterface {
            public function map(mixed $source, mixed $target = null): mixed
            {
                \assert($source instanceof SourceStub);
                \assert($target instanceof TargetStub);

                $target->value = $source->value;

                return $target;
            }
        };

        $locator = $this->createMock(ContainerInterface::class);
        $locator
            ->expects($this->once())
            ->method('get')
            ->with('mapper.source_to_target')
            ->willReturn($mapper);

        $registry = new MapperRegistry(
            mapperIdsByKey: [
                SourceStub::class . '->' . TargetStub::class => 'mapper.source_to_target',
            ],
            mapperLocator: $locator,
        );

        $result = $registry->map($source, $target);

        $this->assertSame($target, $result);
        $this->assertSame('updated', $target->value);
    }

    public function testThrowsWhenMapperDoesNotExist(): void
    {
        $this->expectException(MappingException::class);
        $this->expectExceptionMessage(sprintf(
            'No mapper registered for "%s" -> "%s".',
            SourceStub::class,
            TargetStub::class
        ));

        $registry = new MapperRegistry(
            mapperIdsByKey: [],
            mapperLocator: $this->createStub(ContainerInterface::class),
        );

        $registry->map(new SourceStub('hello'), TargetStub::class);
    }

    public function testThrowsWhenTargetIsScalar(): void
    {
        $this->expectException(MappingException::class);
        $this->expectExceptionMessage('Target cannot be a scalar. Use an object instance, a class-string, or an array.');

        $registry = new MapperRegistry(
            mapperIdsByKey: [],
            mapperLocator: $this->createStub(ContainerInterface::class),
        );

        $registry->map(new SourceStub('hello'), 123);
    }

    public function testThrowsWhenTargetClassDoesNotExist(): void
    {
        $this->expectException(MappingException::class);
        $this->expectExceptionMessage('Target class "App\\DoesNotExist\\Foo" does not exist.');

        $registry = new MapperRegistry(
            mapperIdsByKey: [],
            mapperLocator: $this->createStub(ContainerInterface::class),
        );

        $registry->map(new SourceStub('hello'), 'App\\DoesNotExist\\Foo');
    }

    public function testThrowsWhenTargetClassIsNotInstantiable(): void
    {
        $this->expectException(MappingException::class);
        $this->expectExceptionMessage(sprintf(
            'Target class "%s" is not instantiable.',
            AbstractTargetStub::class
        ));

        $registry = new MapperRegistry(
            mapperIdsByKey: [],
            mapperLocator: $this->createStub(ContainerInterface::class),
        );

        $registry->map(new SourceStub('hello'), AbstractTargetStub::class);
    }

    public function testThrowsWhenTargetClassHasRequiredConstructorArguments(): void
    {
        $this->expectException(MappingException::class);
        $this->expectExceptionMessage(sprintf(
            'Cannot auto-instantiate "%s": constructor has required arguments.',
            TargetWithRequiredConstructorStub::class
        ));

        $registry = new MapperRegistry(
            mapperIdsByKey: [],
            mapperLocator: $this->createStub(ContainerInterface::class),
        );

        $registry->map(new SourceStub('hello'), TargetWithRequiredConstructorStub::class);
    }
}

final class SourceStub
{
    public function __construct(public string $value)
    {
    }
}

final class TargetStub
{
    public string $value = '';
}

abstract class AbstractTargetStub
{
}

final class TargetWithRequiredConstructorStub
{
    public function __construct(public string $value)
    {
    }
}
