<?php

namespace App\Write\Exception;

abstract class WriteException extends \RuntimeException
{
    public function __construct(
        string $message,
        private readonly ?string $field = null,
        ?\Throwable $previous = null,
    ) {
        if ($field !== null) {
            $message = sprintf('%s: %s', $field, $message);
        }

        parent::__construct($message, 0, $previous);
    }

    public function getField(): ?string
    {
        return $this->field;
    }
}
