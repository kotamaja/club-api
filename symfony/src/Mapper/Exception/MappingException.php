<?php

namespace App\Mapper\Exception;

final class MappingException extends \RuntimeException
{
    public static function mapperNotFound(string $source, string $target): self
    {
        return new self(sprintf(
            'No mapper registered for "%s" -> "%s".',
            $source,
            $target
        ));
    }

    public static function targetCannotBeScalar(): self
    {
        return new self(
            'Target cannot be a scalar. Use an object instance, a class-string, or an array.'
        );
    }

    public static function targetClassDoesNotExist(string $class): self
    {
        return new self(sprintf(
            'Target class "%s" does not exist.',
            $class
        ));
    }

    public static function targetNotInstantiable(string $class): self
    {
        return new self(sprintf(
            'Target class "%s" is not instantiable.',
            $class
        ));
    }

    public static function constructorHasRequiredArguments(string $class): self
    {
        return new self(sprintf(
            'Cannot auto-instantiate "%s": constructor has required arguments.',
            $class
        ));
    }
}
