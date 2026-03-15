<?php

namespace App\State\Enum;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Enum\EnumChoiceDto;

final class EnumCollectionProvider implements ProviderInterface
{

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $extra = $operation->getExtraProperties();

        $enumClass = $extra['app.enum_class'] ?? null;
        if (!\is_string($enumClass) || !enum_exists($enumClass)) {
            throw new \LogicException('Missing or invalid "app.enum_class" in extraProperties.');
        }

        if (!is_subclass_of($enumClass, \BackedEnum::class)) {
            throw new \LogicException(sprintf('Enum "%s" must be a backed enum.', $enumClass));
        }

        $domain = $extra['app.enum_i18n_domain'] ?? null;
        if (!\is_string($domain) || $domain === '') {
            $domain = self::toSnakeCase((new \ReflectionClass($enumClass))->getShortName());
        }

        /** @var class-string<\BackedEnum> $enumClass */
        $items = [];
        foreach ($enumClass::cases() as $case) {
            $item = new EnumChoiceDto();
            $item->value = (string) $case->value;
            $item->label = $domain . '.' . $case->value;
            $items[] = $item;
        }

        return $items;
    }

    private static function toSnakeCase(string $s): string
    {
        $s = preg_replace('/(?<!^)[A-Z]/', '_$0', $s) ?? $s;
        return strtolower($s);
    }
}
