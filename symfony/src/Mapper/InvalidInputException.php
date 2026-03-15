<?php

namespace App\Mapper;

class InvalidInputException extends \RuntimeException
{
    public function __construct(
        string $message,
        private readonly ?string $field = null,
    ) {
        parent::__construct($message);
    }

    public function getField(): ?string
    {
        return $this->field;
    }
}
{

}
