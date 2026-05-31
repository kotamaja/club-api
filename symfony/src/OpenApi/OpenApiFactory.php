<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\OpenApi;
use App\Security\OrganizationContext\OrganizationHeader;

final readonly class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated,
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $paths = $openApi->getPaths();

        foreach ($paths->getPaths() as $path => $pathItem) {
            $operations = [
                'get' => $pathItem->getGet(),
                'post' => $pathItem->getPost(),
                'put' => $pathItem->getPut(),
                'patch' => $pathItem->getPatch(),
                'delete' => $pathItem->getDelete(),
            ];

            foreach ($operations as $method => $operation) {
                if (!$operation instanceof Operation) {
                    continue;
                }

                $operation = $this->withOrganizationHeader($operation);

                $pathItem = match ($method) {
                    'get' => $pathItem->withGet($operation),
                    'post' => $pathItem->withPost($operation),
                    'put' => $pathItem->withPut($operation),
                    'patch' => $pathItem->withPatch($operation),
                    'delete' => $pathItem->withDelete($operation),
                };
            }

            $paths->addPath($path, $pathItem);
        }

        return $openApi;
    }

    private function withOrganizationHeader(Operation $operation): Operation
    {
        $parameters = $operation->getParameters() ?? [];

        if ($parameters instanceof \ArrayObject) {
            $parameters = $parameters->getArrayCopy();
        }

        $parameters = array_values($parameters);

        foreach ($parameters as $parameter) {
            if (
                $parameter instanceof Parameter
                && $parameter->getName() === OrganizationHeader::NAME
                && $parameter->getIn() === 'header'
            ) {
                return $operation;
            }
        }

        $parameters[] = new Parameter(
            name: OrganizationHeader::NAME,
            in: 'header',
            description: 'Public identifier of the current organization.',
            required: false,
            schema: [
                'type' => 'string',
                'example' => '01HZ...',
            ],
        );

        return $operation->withParameters($parameters);
    }
}
