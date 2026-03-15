<?php

namespace App\Mapper;

use Doctrine\Common\Util\ClassUtils;
use Psr\Container\ContainerInterface;

final class MapperRegistry implements MapperRegistryInterface
{


    /**
     * @param array<string, string> $mapperIdsByKey key = "Source->Target", value = service id
     */
    public function __construct(
        private readonly array              $mapperIdsByKey,
        private readonly ContainerInterface $mapperLocator, // ServiceLocator<CustomMapperInterface>
    )
    {
    }

    /**
     * @param mixed $source
     * @param mixed $targetOrClass object|string|array
     *
     * @return mixed object|array
     */
    public function map(mixed $source, mixed $targetOrClass): mixed
    {
        $sourceKey = $this->typeKey($source);

        // --- construire target + targetKey ---
        if (\is_string($targetOrClass)) {
            // string => on considère que c'est un FQCN instantiable
            $target = $this->instantiate($targetOrClass);
            $targetKey = $target::class;
        } else {
            $target = $targetOrClass;

            // objet => class, array => 'array', scalar => 'scalar'
            $targetKey = $this->typeKey($target);
            if ($targetKey === 'scalar') {
                throw new \LogicException('Target cannot be a scalar. Use object, class-string, or array.');
            }
        }

        $key = $sourceKey . '->' . $targetKey;

        $serviceId = $this->mapperIdsByKey[$key] ?? null;
        if (!$serviceId) {
            throw new \RuntimeException(sprintf('No custom mapper for %s -> %s', $sourceKey, $targetKey));
        }

        /** @var CustomMapperInterface $mapper */
        $mapper = $this->mapperLocator->get($serviceId);

        return $mapper->map($source, $target);
    }

    private function typeKey(mixed $value): string
    {
        if (\is_object($value)) {
            if (\is_object($value)) {
                return ClassUtils::getClass($value);
            }
        }
        if (\is_array($value)) {
            return 'array';
        }

        return 'scalar';
    }

    private function instantiate(string $class): object
    {
        if (!class_exists($class)) {
            throw new \LogicException(sprintf('Target class "%s" does not exist.', $class));
        }

        $rc = new \ReflectionClass($class);
        if (!$rc->isInstantiable()) {
            throw new \LogicException(sprintf('Target class "%s" is not instantiable.', $class));
        }

        $ctor = $rc->getConstructor();
        if ($ctor && $ctor->getNumberOfRequiredParameters() > 0) {
            throw new \LogicException(sprintf(
                'Cannot auto-instantiate "%s": constructor has required args. Use a factory or pass an existing instance.',
                $class
            ));
        }

        return $rc->newInstance();
    }
}
