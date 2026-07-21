# Skill: DTO conventions for Ramalo / club-api

## Purpose

This skill describes how to create and modify DTO classes in the Ramalo / club-api Symfony API.

The project uses DTOs for API input and output. DTOs must remain simple, explicit and compatible with Symfony Validator and API Platform denormalization.

The conventions below must be followed when generating new DTOs.

---

## Namespaces and locations

DTOs are placed under `App\Dto`.

Use feature-oriented sub-namespaces.

Examples:

```txt
src/Dto/Person/PersonCreateDto.php
src/Dto/Person/PersonPatchDto.php
src/Dto/Event/EventCreateDto.php
src/Dto/Event/EventPatchDto.php
src/Dto/Organization/OrganizationCapabilityItemDto.php
```

The namespace must match the directory.

Example:

```php
namespace App\Dto\Event;
```

---

## Create DTO conventions

Create DTOs use public properties.

Example:

```php
<?php

namespace App\Dto\Person;

use Symfony\Component\Validator\Constraints as Assert;

final class PersonCreateDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    public ?string $firstname = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    public ?string $lastname = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public ?string $email = null;
}
```

Rules:

* Use `final class`.
* Use public properties for create DTOs.
* Use nullable types for input fields when Symfony Validator must report missing values.
* Put validation rules on properties using Symfony Validator attributes.
* Do not put business logic in DTOs.
* Do not inject services into DTOs.
* Do not use constructors for API input DTOs unless there is a strong reason.

---

## Required fields in Create DTOs

Required scalar fields should generally be nullable in PHP but validated with Symfony Validator.

Example:

```php
#[Assert\NotBlank]
#[Assert\Length(max: 180)]
public ?string $title = null;
```

For required dates:

```php
#[Assert\NotNull]
public ?\DateTimeImmutable $startsAt = null;
```

For required enums:

```php
#[Assert\NotNull]
public ?RelationshipType $type = null;
```

This allows API Platform / Symfony Validator to return validation errors instead of producing PHP initialization errors.

---

## Optional fields in Create DTOs

Optional nullable fields should be initialized to `null`.

Example:

```php
#[Assert\Length(max: 255)]
public ?string $location = null;
```

Optional booleans can use a concrete default.

Example:

```php
public bool $waitlistEnabled = false;
```

Optional integers can be nullable and validated when provided.

Example:

```php
#[Assert\Positive]
public ?int $capacity = null;
```

---

## Enums in DTOs

Use PHP enum types directly when Symfony Serializer/API Platform can denormalize them.

Preferred:

```php
use App\Core\Event\Enum\EventType;

public ?EventType $type = null;
```

Avoid using raw strings with `Assert\Choice` when an enum already exists.

Less preferred:

```php
#[Assert\Choice(['general', 'course'])]
public ?string $type = null;
```

If a field has a business default, keep it nullable in the Create DTO and apply the default in the WriteService or entity.

Example:

```php
public ?EventType $type = null;
```

Then in the WriteService:

```php
$event->changeType($input->type ?? EventType::General);
```

---

## Patch DTO conventions

Patch DTOs must distinguish between:

```txt
field absent
field provided with null
field provided with a value
```

Therefore, Patch DTOs use private properties and explicit `Provided` flags.

Example:

```php
<?php

namespace App\Dto\Person;

use Symfony\Component\Validator\Constraints as Assert;

final class PersonPatchDto
{
    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Length(max: 150)]
    private ?string $firstname = null;

    private bool $firstnameProvided = false;

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): void
    {
        $this->firstnameProvided = true;
        $this->firstname = $firstname;
    }

    public function isFirstnameProvided(): bool
    {
        return $this->firstnameProvided;
    }
}
```

Rules:

* Use `final class`.
* Use private properties.
* For each patchable field, add a boolean `<field>Provided` flag.
* The setter must set the provided flag to `true`.
* The WriteService must check `is<Field>Provided()` before applying a change.
* Do not use public properties for Patch DTOs.
* Do not infer whether a field was provided from its value.

---

## Nullable fields in Patch DTOs

If a field is nullable in the domain and `null` means “clear the value”, allow null.

Example:

```php
#[Assert\Length(max: 255)]
private ?string $location = null;

private bool $locationProvided = false;
```

Then:

```txt
location absent       -> do not change location
location = null       -> clear location
location = "Lausanne" -> update location
```

The WriteService must implement this explicitly:

```php
if ($input->isLocationProvided()) {
    $event->changeLocation($input->getLocation());
}
```

---

## Non-nullable fields in Patch DTOs

If a field is required in the domain but optional in PATCH, use a nullable property with `Assert\NotBlank(allowNull: true)` for strings.

Example:

```php
#[Assert\NotBlank(allowNull: true)]
#[Assert\Length(max: 180)]
private ?string $title = null;
```

This means:

```txt
title absent     -> no change
title = null     -> validation/business rejection
title = ""       -> validation rejection
title = "..."    -> update
```

For enums that cannot be null when provided, use `Assert\NotNull`.

Example:

```php
#[Assert\NotNull]
private ?EventType $type = null;
```

This means:

```txt
type absent      -> no change
type = null      -> validation rejection
type = "course"  -> update
```

---

## Date fields in Patch DTOs

Date fields should use `?\DateTimeImmutable`.

Example:

```php
private ?\DateTimeImmutable $startsAt = null;
private bool $startsAtProvided = false;

public function getStartsAt(): ?\DateTimeImmutable
{
    return $this->startsAt;
}

public function setStartsAt(?\DateTimeImmutable $startsAt): void
{
    $this->startsAtProvided = true;
    $this->startsAt = $startsAt;
}

public function isStartsAtProvided(): bool
{
    return $this->startsAtProvided;
}
```

Cross-field validation, such as `endsAt > startsAt`, should usually be handled in the entity or WriteService, not only in the DTO.

---

## WriteService usage

WriteServices must apply Patch DTOs only when fields were provided.

Example:

```php
if ($input->isTitleProvided()) {
    $event->rename($input->getTitle());
}

if ($input->isLocationProvided()) {
    $event->changeLocation($input->getLocation());
}

if ($input->isCapacityProvided()) {
    $event->changeCapacity($input->getCapacity());
}
```

Do not write this:

```php
if ($input->getTitle() !== null) {
    $event->rename($input->getTitle());
}
```

because it loses the distinction between “absent” and “provided with null”.

---

## DTOs and business logic

DTOs must not contain business logic.

Allowed in DTOs:

```txt
properties
getters
setters
provided flags
Symfony Validator attributes
simple Assert expressions
```

Not allowed in DTOs:

```txt
Doctrine repositories
EntityManager
service injection
permission checks
feature checks
business decisions
entity mutations
```

Business rules belong in entities, WriteServices, policies, processors or dedicated domain services.

---

## Validation conventions

Use Symfony Validator attributes.

Common constraints:

```php
#[Assert\NotBlank]
#[Assert\NotBlank(allowNull: true)]
#[Assert\NotNull]
#[Assert\Length(max: 180)]
#[Assert\Email]
#[Assert\Ulid]
#[Assert\Positive]
```

Use `Assert\Ulid` for public IDs represented as strings.

Use enum types directly instead of `Assert\Choice` when a PHP enum exists.

Use `Assert\Expression` only for simple DTO-level validation.

Complex validation belongs in the WriteService or the entity.

---

## Naming conventions

Use these suffixes:

```txt
CreateDto
PatchDto
ListDto
ItemDto
DetailDto
```

Examples:

```txt
EventCreateDto
EventPatchDto
EventListDto
EventItemDto
OrganizationCapabilityItemDto
```

Avoid vague names such as:

```txt
EventDto
DataDto
RequestDto
PayloadDto
```

unless there is a very specific reason.

---

## Namespace size

Avoid putting too many DTO classes in a single namespace.

If a namespace grows beyond roughly ten classes, split it into sub-namespaces.

Example:

```txt
App\Dto\Event
App\Dto\Event\Registration
App\Dto\Event\PublicRegistration
```

Prefer small, responsibility-focused namespaces.

---

## Summary

Create DTOs:

```txt
public properties
nullable input fields when validation should handle missing values
Symfony Validator attributes
no business logic
```

Patch DTOs:

```txt
private properties
provided flags
getters and setters
Symfony Validator attributes
WriteService checks is<Field>Provided()
```

Enums:

```txt
use PHP enum types directly
apply defaults in WriteService/entity when appropriate
```

DTOs must remain simple API boundary objects.
