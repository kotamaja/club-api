<?php

namespace App\Dto\ConnectionUser;

class ConnectionUserItemDto
{
    public string $id;

    public string $email;

    public string $status;

    public array $roles = [];

    public bool $enabled ;

}
