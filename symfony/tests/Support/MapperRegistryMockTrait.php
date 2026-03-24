<?php

namespace App\Tests\Support;

use App\Mapper\MapperRegistryInterface;

trait MapperRegistryMockTrait
{
    /**
     * @param array<int, array{0: object, 1: string, 2: mixed}> $map
     */
    private function createMapperRegistryMock(array $map): MapperRegistryInterface
    {
        $mapperRegistry = $this->createStub(MapperRegistryInterface::class);

        $mapperRegistry
            ->method('map')
            ->willReturnCallback(function (object $source, string $targetClass) use ($map) {
                foreach ($map as [$expectedSource, $expectedTargetClass, $result]) {
                    if ($source === $expectedSource && $targetClass === $expectedTargetClass) {
                        return $result;
                    }
                }

                $this->fail(sprintf(
                    'Unexpected mapperRegistry->map() call with source %s and target %s',
                    get_debug_type($source),
                    $targetClass
                ));
            });

        return $mapperRegistry;
    }
}
