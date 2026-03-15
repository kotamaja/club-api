<?php

namespace App\DependencyInjection\Compiler;

use App\Mapper\MapperRegistry;
use App\Mapper\Maps;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MapperRegistryCompilerPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(MapperRegistry::class)) {
            return;
        }

        $mapperIdsByKey = [];
        $locatorMap = [];

        foreach ($container->findTaggedServiceIds('app.mapper') as $id => $tags) {
            $def = $container->getDefinition($id);
            $class = $def->getClass();
            if (!$class) {
                continue;
            }

            $refl = new ReflectionClass($class);
            $attrs = $refl->getAttributes(Maps::class);

            if (\count($attrs) !== 1) {
                throw new \LogicException(sprintf(
                    '%s must declare exactly one #[%s]',
                    $class,
                    Maps::class
                ));
            }

            /** @var Maps $mapsAttr */
            $mapsAttr = $attrs[0]->newInstance();
            $key = $mapsAttr->source.'->'.$mapsAttr->target;

            if (isset($mapperIdsByKey[$key])) {
                throw new \LogicException(sprintf(
                    'Duplicate mapper for %s -> %s',
                    $mapsAttr->source,
                    $mapsAttr->target
                ));
            }

            // 1) table clé => service id (string)
            $mapperIdsByKey[$key] = $id;

            // 2) map du ServiceLocator : serviceId => Reference(serviceId)
            $locatorMap[$id] = new Reference($id);
        }

        if (!$mapperIdsByKey) {
            throw new \RuntimeException('No mappers have been defined.');
        }

        // Crée un ServiceLocator lazy pour ces mappers
        $locatorRef = ServiceLocatorTagPass::register($container, $locatorMap);

        $container->getDefinition(MapperRegistry::class)->setArguments([
            $mapperIdsByKey,
            $locatorRef,
        ]);
    }
}
