# Skill: Output DTO and Mapper conventions for Ramalo / club-api

## Purpose

This skill describes how to create and modify output DTOs in the Ramalo / club-api Symfony API.

Output DTOs are used for API responses, typically with API Platform operations such as `Get` and `GetCollection`.

The project uses a custom mapper system:

* `MapperRegistryInterface`
* `MapperRegistry`
* `CustomMapperInterface`
* `#[Maps(source: ..., target: ...)]`
* `CollectionProvider`
* `ItemProvider`
* `MappingPaginator`

When generating output DTOs or mappers, follow the conventions below.

---

## Output DTO naming

Use explicit DTO suffixes depending on the operation.

Common names:

```txt
XxxListDto
XxxItemDto
XxxDetailDto
XxxSummaryDto
```

Preferred examples:

```txt
PersonListDto
PersonItemDto
ClubListDto
ClubItemDto
EventListDto
EventItemDto
OrganizationCapabilityItemDto
```

Avoid vague DTO names such as:

```txt
EventDto
DataDto
ResponseDto
PayloadDto
```

unless there is a very specific reason.

---

## Output DTO location

DTOs are placed under `src/Dto`.

Use feature-oriented namespaces.

Examples:

```txt
src/Dto/Person/PersonListDto.php
src/Dto/Person/PersonItemDto.php
src/Dto/Club/ClubListDto.php
src/Dto/Club/ClubItemDto.php
src/Dto/Event/EventListDto.php
src/Dto/Event/EventItemDto.php
```

The namespace must match the directory.

Example:

```php
namespace App\Dto\Event;
```

If a namespace becomes too crowded, split into sub-namespaces.

Example:

```txt
App\Dto\Event
App\Dto\Event\Registration
App\Dto\Event\PublicRegistration
```

Avoid putting too many DTOs in the same namespace.

---

## Output DTO structure

Output DTOs must be simple mutable data objects.

Use:

```txt
final class
public properties
no constructor with required parameters
no readonly class
no business logic
```

Reason: the custom `MapperRegistry` may instantiate DTOs with reflection using a no-argument constructor.

Do this:

```php
<?php

namespace App\Dto\Club;

final class ClubItemDto
{
    public string $id;

    public string $name;
}
```

Do not do this for mapped output DTOs:

```php
final readonly class ClubItemDto
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
    }
}
```

because this is not compatible with the current generic mapper instantiation style.

---

## Nullable properties and defaults

Nullable fields should be initialized to `null`.

Example:

```php
public ?string $description = null;

public ?string $clubId = null;

public ?string $clubName = null;
```

Scalar counters and booleans may have safe defaults.

Example:

```php
public int $registeredCount = 0;

public int $waitlistedCount = 0;

public bool $waitlistEnabled = false;

public bool $publicRegistrationEnabled = false;
```

Required fields that are always assigned by the mapper may be left uninitialized.

Example:

```php
public string $id;

public string $title;

public \DateTimeImmutable $startsAt;
```

---

## List DTO vs Item DTO

Use `XxxListDto` for collection responses.

A List DTO should be lightweight and contain only the fields needed to display a list, table, search result, calendar entry, or card.

Example:

```php
<?php

namespace App\Dto\Event;

final class EventListDto
{
    public string $id;

    public string $title;

    public string $type;

    public string $status;

    public ?string $clubId = null;

    public ?string $clubName = null;

    public ?string $location = null;

    public \DateTimeImmutable $startsAt;

    public \DateTimeImmutable $endsAt;

    public string $timezone;

    public bool $allDay;

    public ?int $capacity = null;

    public int $registeredCount = 0;

    public bool $waitlistEnabled = false;

    public bool $publicRegistrationEnabled = false;

    public ?string $myRegistrationStatus = null;
}
```

Use `XxxItemDto` for item/detail responses.

An Item DTO may contain more complete information than the List DTO.

Example:

```php
<?php

namespace App\Dto\Event;

final class EventItemDto
{
    public string $id;

    public string $title;

    public ?string $description = null;

    public ?string $location = null;

    public string $type;

    public string $status;

    public ?string $clubId = null;

    public ?string $clubName = null;

    public \DateTimeImmutable $startsAt;

    public \DateTimeImmutable $endsAt;

    public string $timezone;

    public bool $allDay;

    public ?int $capacity = null;

    public int $registeredCount = 0;

    public int $waitlistedCount = 0;

    public bool $waitlistEnabled = false;

    public bool $publicRegistrationEnabled = false;

    public ?\DateTimeImmutable $registrationStartsAt = null;

    public ?\DateTimeImmutable $registrationEndsAt = null;

    public ?string $myRegistrationStatus = null;
}
```

---

## Organization fields in output DTOs

Most resources are returned in the context of the current organization, selected by the `X-Organization-Id` header.

Do not add these fields by default to every DTO:

```php
public string $organizationId;

public string $organizationName;
```

Only expose organization fields when they are explicitly useful for the API use case.

For normal organization-scoped resources such as people, clubs, memberships and events, the organization is implicit.

---

## Public identifiers

API DTOs should expose public identifiers, not internal database IDs.

Use the entity public ID getter.

Example:

```php
$dto->id = $source->getPublicId();
```

If the project convention for `getPublicId()` returns a `string`, assign it directly.

If it returns a `Ulid` object, convert it according to the existing project convention, for example:

```php
$dto->id = $source->getPublicId()->toRfc4122();
```

or:

```php
$dto->id = (string) $source->getPublicId();
```

Always follow the convention already used by nearby mappers.

Do not expose internal numeric database IDs.

---

## Enum fields in output DTOs

Expose enum values as strings in output DTOs.

Example:

```php
$dto->type = $source->getType()->value;

$dto->status = $source->getStatus()->value;
```

DTO output fields should usually be typed as `string`, not as enum objects.

Preferred:

```php
public string $type;

public string $status;
```

Avoid:

```php
public EventType $type;

public EventStatus $status;
```

unless there is a specific reason.

---

## Custom mapper location

Custom mappers are placed under:

```txt
src/Mapper/CustomMapper/<Feature>/
```

Examples:

```txt
src/Mapper/CustomMapper/Club/ClubToClubItemDtoMapper.php
src/Mapper/CustomMapper/Event/EventToEventListDtoMapper.php
src/Mapper/CustomMapper/Event/EventToEventItemDtoMapper.php
```

Use one mapper per source-target pair.

---

## Custom mapper declaration

Every custom mapper must implement `CustomMapperInterface`.

Every custom mapper must declare a `#[Maps]` attribute.

Example:

```php
<?php

namespace App\Mapper\CustomMapper\Club;

use App\Dto\Club\ClubItemDto;
use App\Entity\Club;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: Club::class, target: ClubItemDto::class)]
final class ClubToClubItemDtoMapper implements CustomMapperInterface
{
    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof Club) {
            throw new \LogicException('Invalid mapper usage.');
        }

        $dto = $target instanceof ClubItemDto ? $target : new ClubItemDto();

        $dto->id = $source->getPublicId();
        $dto->name = $source->getName();

        return $dto;
    }
}
```

---

## Mapper method pattern

Mapper methods should follow this pattern:

```php
public function map(mixed $source, mixed $target = null): mixed
{
    if (!$source instanceof SourceClass) {
        throw new \LogicException('Invalid mapper usage.');
    }

    $dto = $target instanceof TargetDto ? $target : new TargetDto();

    // assign fields

    return $dto;
}
```

Do not silently accept the wrong source type.

Do not return arrays when the target is a DTO class.

Do not mutate entities inside mappers.

---

## Mapper dependencies

Custom mappers may inject services through the constructor.

Example:

```php
final readonly class EventToEventItemDtoMapper implements CustomMapperInterface
{
    public function __construct(
        private CurrentOrganizationContext $currentOrganizationContext,
        private EventRegistrationRepository $eventRegistrationRepository,
    ) {
    }

    public function map(mixed $source, mixed $target = null): mixed
    {
        // ...
    }
}
```

This is allowed when the DTO needs contextual or computed fields.

However, avoid putting heavy business logic in mappers.

Mappers may:

```txt
read entity data
read simple contextual data
call repositories for simple DTO enrichment
compute simple presentation fields
```

Mappers must not:

```txt
persist entities
flush the EntityManager
perform write operations
perform permission-changing logic
make complex business decisions
```

---

## Contextual output fields

Some output fields depend on the current user or current organization context.

Example:

```php
public ?string $myRegistrationStatus = null;
```

For events, `myRegistrationStatus` means the active registration status of the current person for the event.

Allowed values:

```txt
registered
waitlisted
null
```

Do not expose `cancelled` as the current status for this field. Cancelled registrations are historical and should not be treated as the current active state.

The field represents only active registrations.

---

## Computing contextual fields in mappers

For the first version, it is acceptable to compute simple contextual fields inside the mapper.

Example:

```php
private function resolveMyRegistrationStatus(Event $event): ?string
{
    $person = $this->currentOrganizationContext->getPerson();

    if ($person === null) {
        return null;
    }

    return $this->eventRegistrationRepository
        ->findActiveRegistrationForPerson($event, $person)
        ?->getStatus()
        ->value;
}
```

Then in the mapper:

```php
$dto->myRegistrationStatus = $this->resolveMyRegistrationStatus($source);
```

This may produce one additional query per mapped event in collection endpoints. This is accepted for V1 when collections are paginated.

If performance becomes an issue, create a dedicated collection provider or batch enrichment service.

---

## Avoiding N+1 queries later

When mapping collections, contextual fields can cause N+1 queries.

Example:

```txt
1 query to fetch events
+ 1 query per event to fetch the current person's registration status
```

For V1, this is acceptable if the collection is paginated and the mapper logic remains simple.

If needed later, optimize with a dedicated provider:

```txt
EventCollectionProvider
EventDtoEnricher
CurrentPersonEventRegistrationResolver
```

The optimized provider should:

```txt
load the current page of events
fetch all active registrations for the current person and these events in one query
map each event
inject the status from a precomputed map
return the mapped paginator
```

Do not prematurely introduce this complexity unless needed.

---

## Repository methods for contextual fields

Prefer explicit repository methods for contextual lookups.

Example:

```php
public function findActiveRegistrationForPerson(Event $event, Person $person): ?EventRegistration
{
    return $this->createQueryBuilder('registration')
        ->andWhere('registration.event = :event')
        ->andWhere('registration.person = :person')
        ->andWhere('registration.status IN (:statuses)')
        ->setParameter('event', $event)
        ->setParameter('person', $person)
        ->setParameter('statuses', [
            EventRegistrationStatus::Registered,
            EventRegistrationStatus::Waitlisted,
        ])
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
}
```

Do not filter large Doctrine collections in memory when a targeted repository query is more appropriate.

Avoid this in list mappers if it may load many registrations:

```php
$event->getRegistrations()->filter(...);
```

For item/detail mappers, small in-memory calculations may be acceptable when the collection is already available.

---

## Count fields

Count fields may be exposed directly in DTOs when useful for the frontend.

Examples:

```php
public int $registeredCount = 0;

public int $waitlistedCount = 0;
```

Prefer clear names:

```txt
registeredCount
waitlistedCount
```

Avoid ambiguous names:

```txt
count
total
number
```

When the count is business-specific, implement the logic in the entity, repository, or a small private mapper method.

Example:

```php
$dto->registeredCount = $source->getRegisteredCount();
```

For waitlist count:

```php
private function getWaitlistedCount(Event $event): int
{
    return $event->getRegistrations()
        ->filter(static fn (EventRegistration $registration): bool => $registration->getStatus() === EventRegistrationStatus::Waitlisted)
        ->count();
}
```

If this becomes inefficient, move the calculation to a query or provider.

---

## API Platform GetCollection operation

Collection operations should use the generic `CollectionProvider` when simple entity-to-DTO mapping is enough.

Example:

```php
new GetCollection(
    uriTemplate: '/events',
    output: EventListDto::class,
    provider: CollectionProvider::class,
    parameters: [
        // query parameters
    ],
),
```

The `CollectionProvider` resolves the output DTO class from the operation and maps each entity through `MapperRegistry`.

Therefore, the output DTO must be compatible with mapper instantiation.

---

## API Platform Get operation

Item operations should use the generic `ItemProvider` when simple entity-to-DTO mapping is enough.

Example:

```php
new Get(
    uriTemplate: '/events/{id}',
    uriVariables: [
        'id' => new Link(fromClass: Event::class, identifiers: ['publicId']),
    ],
    output: EventItemDto::class,
    provider: ItemProvider::class,
),
```

Use public IDs in API routes.

---

## Query parameters

Use explicit `QueryParameter` definitions on ApiResource operations.

Common filters:

```php
use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use ApiPlatform\Doctrine\Orm\Filter\PartialSearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\SortFilter;
```

Use `ExactFilter` for public IDs, enums, statuses and relations.

Use `PartialSearchFilter` for searchable text fields.

Use `SortFilter` for ordering.

Example:

```php
'id' => new QueryParameter(
    schema: [
        'type' => 'array',
        'items' => ['type' => 'string'],
        'uniqueItems' => true,
    ],
    filter: new ExactFilter(),
    property: 'publicId',
    constraints: [
        new Assert\All([
            new Assert\NotBlank(),
            new Assert\Ulid(),
        ]),
    ],
    castToArray: true,
),
```

For relation public IDs:

```php
'clubId' => new QueryParameter(
    schema: [
        'type' => 'array',
        'items' => ['type' => 'string'],
        'uniqueItems' => true,
    ],
    filter: new ExactFilter(),
    property: 'club.publicId',
    constraints: [
        new Assert\All([
            new Assert\NotBlank(),
            new Assert\Ulid(),
        ]),
    ],
    castToArray: true,
),
```

For text:

```php
'title' => new QueryParameter(
    filter: new PartialSearchFilter(),
    property: 'title',
),
```

For enums:

```php
'status' => new QueryParameter(
    filter: new ExactFilter(),
    property: 'status',
),
```

For sorting:

```php
'orderStartsAt' => new QueryParameter(
    filter: new SortFilter(),
    property: 'startsAt',
),
```

---

## ApiResource example for Event read operations

Example for `Event` read operations:

```php
use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use ApiPlatform\Doctrine\Orm\Filter\PartialSearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\SortFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\QueryParameter;
use App\Dto\Event\EventItemDto;
use App\Dto\Event\EventListDto;
use App\State\CollectionProvider;
use App\State\ItemProvider;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/events',
            output: EventListDto::class,
            provider: CollectionProvider::class,
            parameters: [
                'id' => new QueryParameter(
                    schema: [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                        'uniqueItems' => true,
                    ],
                    filter: new ExactFilter(),
                    property: 'publicId',
                    constraints: [
                        new Assert\All([
                            new Assert\NotBlank(),
                            new Assert\Ulid(),
                        ]),
                    ],
                    castToArray: true,
                ),
                'clubId' => new QueryParameter(
                    schema: [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                        'uniqueItems' => true,
                    ],
                    filter: new ExactFilter(),
                    property: 'club.publicId',
                    constraints: [
                        new Assert\All([
                            new Assert\NotBlank(),
                            new Assert\Ulid(),
                        ]),
                    ],
                    castToArray: true,
                ),
                'title' => new QueryParameter(
                    filter: new PartialSearchFilter(),
                    property: 'title',
                ),
                'location' => new QueryParameter(
                    filter: new PartialSearchFilter(),
                    property: 'location',
                ),
                'type' => new QueryParameter(
                    filter: new ExactFilter(),
                    property: 'type',
                ),
                'status' => new QueryParameter(
                    filter: new ExactFilter(),
                    property: 'status',
                ),
                'orderId' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'publicId',
                ),
                'orderTitle' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'title',
                ),
                'orderStartsAt' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'startsAt',
                ),
                'orderEndsAt' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'endsAt',
                ),
                'orderStatus' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'status',
                ),
            ],
        ),
        new Get(
            uriTemplate: '/events/{id}',
            uriVariables: [
                'id' => new Link(fromClass: self::class, identifiers: ['publicId']),
            ],
            output: EventItemDto::class,
            provider: ItemProvider::class,
        ),
    ],
    routePrefix: '/v1',
)]
```

When the attribute is placed directly on the entity class, prefer:

```php
fromClass: self::class
```

instead of importing the entity class itself.

---

## When to create a dedicated provider

Use the generic `CollectionProvider` and `ItemProvider` by default.

Create a dedicated provider only when needed.

Good reasons for a dedicated provider:

```txt
batch enrichment is required
N+1 queries become a real problem
the DTO depends heavily on the current user
the query must be customized beyond standard filters
the result is not a simple entity-to-DTO mapping
```

Avoid dedicated providers for simple mappings.

---

## Output DTOs must not replace authorization

DTOs may expose useful presentation fields, but they must not replace authorization or security checks.

Do not rely only on frontend visibility.

Examples:

```txt
myRegistrationStatus can help Angular display the right badge.
It must not be the only protection for registration/cancellation endpoints.
```

Write operations must still check permissions, features, limits, status and business rules in processors, write services, policies or voters.

---

## Summary

Output DTOs:

```txt
final class
public properties
no required constructor
no readonly
no business logic
public IDs only
enum values as strings
```

Custom mappers:

```txt
one mapper per source-target pair
#[Maps(source: ..., target: ...)]
validate source type
instantiate target if needed
assign public DTO properties
may inject services for simple contextual enrichment
must not write or flush entities
```

Generic providers:

```txt
CollectionProvider for GetCollection
ItemProvider for Get
DTO class resolved from operation output
mapping delegated to MapperRegistry
```

Contextual fields:

```txt
acceptable in V1 mapper when simple
watch for N+1 queries in collections
optimize later with dedicated provider or batch enricher if needed
```
