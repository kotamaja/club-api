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

use ApiPlatform\Doctrine\Orm\State\ItemProvider as DoctrineItemProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Mapper\MapperRegistry;

class ItemProvider  implements ProviderInterface
{
    public function __construct(
        private readonly DoctrineItemProvider $itemProvider,
        private readonly MapperRegistry $mapperRegistry,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $dtoClass = $this->resolveOutputDto($operation);

        $entity = $this->itemProvider->provide($operation, $uriVariables, $context);
        if (!$entity) {
            return null;
        }

        return $this->mapperRegistry->map($entity, $dtoClass);
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
