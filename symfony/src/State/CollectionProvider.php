<?php
/*
 * Copyright (C)  2026 Ville de Lausanne/Service Mobilité et aménagement de l'espace public
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\State;


use ApiPlatform\Doctrine\Orm\State\CollectionProvider as DoctrineCollectionProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\ProviderInterface;
use App\Mapper\MapperRegistry;

class CollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly DoctrineCollectionProvider $collectionProvider,
        private readonly MapperRegistry             $mapperRegistry,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $dtoClass = $this->resolveOutputDto($operation);

        $result = $this->collectionProvider->provide($operation, $uriVariables, $context);

        if (!$result) {
            return [];
        }

        $mapFn = fn(object $entity) => $this->mapperRegistry->map($entity, $dtoClass);

        if ($result instanceof PaginatorInterface && $result instanceof \Traversable) {
            return new MappingPaginator(
                paginator: $result,
                items: $result,
                mapFn: $mapFn,
            );
        }

        $out = [];
        foreach ($result as $entity) {
            $out[] = $mapFn($entity);
        }

        return $out;
    }

    private function resolveOutputDto(Operation $operation): string
    {
        $output = $operation->getOutput();

        if (\is_array($output) && isset($output['class']) && \is_string($output['class'])) {
            return $output['class'];
        }

        if (\is_string($output)) {
            return $output;
        }

        throw new \LogicException('Missing/invalid "output" class on operation. Configure output: YourDto::class');
    }


}
