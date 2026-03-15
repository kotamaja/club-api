<?php

namespace App\State\Util;

use ApiPlatform\Metadata\Operation;

trait OutputDtoResolverTrait
{
    protected function resolveOutputDto(Operation $operation): string
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
