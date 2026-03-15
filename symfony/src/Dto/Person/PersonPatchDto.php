<?php

namespace App\Dto\Person;


use Symfony\Component\Validator\Constraints as Assert;

final class PersonPatchDto
{

    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Length(max: 150)]
    private ?string $firstname;

    private bool $firstnameProvided = false;

    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Length(max: 150)]
    private ?string $lastname;

    private bool $lastnameProvided = false;

    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    private ?string $email;

    private bool $emailProvided = false;

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): void
    {
        $this->firstnameProvided = true;
        $this->firstname = $firstname;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): void
    {
        $this->lastnameProvided = true;
        $this->lastname = $lastname;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->emailProvided = true;
        $this->email = $email;
    }

    public function isFirstnameProvided(): bool
    {
        return $this->firstnameProvided;
    }

    public function isLastnameProvided(): bool
    {
        return $this->lastnameProvided;
    }

    public function isEmailProvided(): bool
    {
        return $this->emailProvided;
    }



}
