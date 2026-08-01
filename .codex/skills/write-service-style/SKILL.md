# Write Service Style

Use this skill when creating or modifying application write services, processors, and API write operations.

## Purpose

Write services contain application-level write logic.

They are responsible for coordinating:

* organization scope checks
* permission checks
* reference resolution
* calls to domain services or entity methods
* conversion of domain exceptions into application write exceptions

They should keep API processors thin and avoid putting business write logic directly in processors.

## Location

Place write services under:

```txt
src/Write/<Feature>/
```

Examples:

```txt
src/Write/Event/EventWriteService.php
src/Write/EventRegistration/EventRegistrationWriteService.php
```

Use one feature folder per write domain.

## Typical files

For a write feature, prefer this structure:

```txt
src/Write/<Feature>/
  <Feature>WriteServiceInterface.php
  <Feature>WriteService.php
  <Feature>PermissionChecker.php
```

Optional result DTOs can be added when the write operation needs to return both an entity and metadata about the operation.

Example:

```txt
src/Write/Event/EventPatchResult.php
```

## Interface

Expose only application actions needed by the API or other application services.

Example:

```php
interface EventRegistrationWriteServiceInterface
{
    public function create(EventRegistrationCreateDto $input, Event $event, ConnectionUser $actor): EventRegistration;

    public function cancel(EventRegistration $registration, ConnectionUser $actor): EventRegistration;
}
```

Use business verbs:

```txt
create
patch
delete
publish
cancel
archive
register
```

Avoid leaking HTTP concepts into the service name or method names.

## No flush in write services

Write services must not call `flush()`.

They may call:

```php
$this->em->persist($entity);
$this->em->remove($entity);
```

but the processor is responsible for flushing.

This keeps transaction boundaries consistent across processors.

## Processor responsibility

Processors should be thin.

They should:

* validate the input DTO type
* retrieve the current actor
* retrieve the entity from previous data or URI variables when needed
* call the write service
* flush through the abstract processor
* map the output DTO

Processors should not contain business write logic.

## Abstract processors

Use the existing abstract processors whenever possible.

For create operations:

```php
final class EventCreateProcessor extends AbstractCreateProcessor
{
    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof EventCreateDto) {
            throw new \LogicException('Expected EventCreateDto.');
        }
    }

    protected function createEntity(mixed $data, array $context): object
    {
        \assert($data instanceof EventCreateDto);

        return $this->eventWriteService->create(
            $data,
            $this->getCurrentConnectionUser(),
        );
    }
}
```

For patch operations:

```php
final class EventPatchProcessor extends AbstractPatchProcessor
{
    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof EventPatchDto) {
            throw new \LogicException('Expected EventPatchDto.');
        }
    }

    protected function entityClass(): string
    {
        return Event::class;
    }

    protected function patchEntity(mixed $data, object $entity, array $context): void
    {
        \assert($data instanceof EventPatchDto);
        \assert($entity instanceof Event);

        $this->eventWriteService->patch(
            $data,
            $entity,
            $this->getCurrentConnectionUser(),
        );
    }
}
```

For delete operations:

```php
final class EventDeleteProcessor extends AbstractDeleteProcessor
{
    protected function entityClass(): string
    {
        return Event::class;
    }

    protected function deleteEntity(object $entity, array $context): void
    {
        \assert($entity instanceof Event);

        $this->eventWriteService->delete(
            $entity,
            $this->getCurrentConnectionUser(),
        );
    }
}
```

For action operations, use a dedicated abstract action processor when several actions share the same pattern.

Example:

```txt
AbstractEventActionProcessor
EventPublishProcessor
EventCancelProcessor
EventArchiveProcessor
```

or:

```txt
AbstractEventRegistrationActionProcessor
EventRegistrationCancelProcessor
```

Action processors must flush after applying the action.

## URI variables in nested operations

For nested create operations, such as:

```txt
POST /api/v1/events/{eventId}/registrations
```

the input DTO should not repeat the parent identifier.

Prefer:

```php
final class EventRegistrationCreateDto
{
    public ?string $personId = null;
    public ?string $membershipId = null;
    public ?string $note = null;
}
```

The processor resolves the parent resource from URI variables.

Example:

```php
private function getEvent(array $context): Event
{
    $eventId = $context['uriVariables']['eventId'] ?? null;

    if (!\is_string($eventId) || $eventId === '') {
        throw new ReferencedResourceNotFoundException('Event not found.', 'eventId');
    }

    $event = $this->eventRepository->findOneBy([
        'publicId' => $eventId,
    ]);

    if (!$event instanceof Event) {
        throw new ReferencedResourceNotFoundException('Event not found.', 'eventId');
    }

    return $event;
}
```

## Organization scope

Every write service must enforce organization scope.

For an entity being modified:

```php
$this->organizationScopeGuard->assertBelongsToCurrentOrganization($event);
```

For referenced resources:

```php
$person = $this->personRepository->findOneBy(['publicId' => $publicId]);

if (!$person instanceof Person) {
    throw new ReferencedResourceNotFoundException('Person not found.', 'personId');
}

$this->organizationScopeGuard->assertBelongsToCurrentOrganization($person);
```

If a referenced resource exists but belongs to another organization, return it as not found.

This avoids leaking cross-organization information.

## Permission checker

Each write feature should have a permission checker.

Example:

```php
final class EventRegistrationPermissionChecker
{
    public function assertCanCreate(ConnectionUser $actor, Event $event): void
    {
        // TODO: implement real permission check.
    }

    public function assertCanCancel(ConnectionUser $actor, EventRegistration $registration): void
    {
        // TODO: implement real permission check.
    }
}
```

Permission methods should be called by the write service, not by the processor.

## Domain services and entities

Write services may call domain services:

```php
$registration = $this->eventRegistrationService->register(
    event: $event,
    person: $person,
    membership: $membership,
    now: new \DateTimeImmutable(),
);
```

They may also call entity methods:

```php
$event->publish();
$registration->changeNote($input->note);
```

Prefer domain services when the operation coordinates policies, repositories, or multiple rules.

Prefer entity methods for simple state changes and invariants local to the entity.

## Exception conversion

Domain exceptions should not leak directly to API Platform.

Convert domain exceptions into application write exceptions inside the write service.

Application write exceptions:

```txt
ReferencedResourceNotFoundException -> 404
ResourceConflictException           -> 409
BusinessRuleViolationException      -> 422
```

Example:

```php
try {
    $registration = $this->eventRegistrationService->register(
        event: $event,
        person: $person,
        membership: $membership,
        now: new \DateTimeImmutable(),
    );
} catch (ActiveEventRegistrationAlreadyExistsException $e) {
    throw new BusinessRuleViolationException(
        'An active registration already exists for this event and person.',
        'personId',
        $e,
    );
} catch (EventRegistrationNotAllowedException $e) {
    throw new BusinessRuleViolationException(
        'Event does not accept registrations.',
        'eventId',
        $e,
    );
} catch (EventCapacityExceededException $e) {
    throw new BusinessRuleViolationException(
        'Event capacity has been reached.',
        'capacity',
        $e,
    );
}
```

Do not catch broad exceptions such as `\RuntimeException` unless there is a very specific reason.

## Field names in write exceptions

When throwing a write exception caused by a specific input field, pass the field name.

Example:

```php
throw new ReferencedResourceNotFoundException('Person not found.', 'personId');
```

The resulting error message will include the field context.

## 404 versus 422

Use `404` when a referenced resource cannot be used because it is missing or outside the current organization scope.

Examples:

```txt
personId does not exist -> 404
membershipId belongs to another organization -> 404
clubId belongs to another organization -> 404
```

Use `422` when the payload is valid and the resources exist, but the business rule rejects the operation.

Examples:

```txt
event does not accept registrations -> 422
duplicate active registration -> 422
event capacity reached -> 422
invalid date range -> 422
```

Use `409` mainly for true conflicts, especially database uniqueness conflicts or resource state conflicts that are better represented as conflict semantics.

## Idempotent actions

Some actions may be intentionally idempotent.

Example:

```txt
POST /api/v1/event-registrations/{id}/cancel
```

Rules:

```txt
registered -> cancelled
waitlisted -> cancelled
cancelled  -> cancelled
```

A repeated cancellation should return `200` and keep the original `cancelledAt` unchanged.

The entity should enforce this:

```php
public function cancel(\DateTimeImmutable $now): void
{
    if ($this->status === EventRegistrationStatus::Cancelled) {
        return;
    }

    $this->status = EventRegistrationStatus::Cancelled;
    $this->cancelledAt = $now;
}
```

## Nested collections

For nested collections, prefer using the standard `CollectionProvider` when filters, sorting, and pagination are needed.

Example:

```txt
GET /api/v1/events/{eventId}/registrations
```

The response should keep the standard collection shape:

```json
{
  "items": [],
  "pagination": {
    "page": 1,
    "itemsPerPage": 30,
    "lastPage": 1,
    "totalItems": 0
  }
}
```

A collection with no visible items should return `200 OK` with an empty `items` array.

Use `404` for item operations when the target resource is not visible or does not exist.

## Tests

Prefer HTTP/API tests for write services because they validate the full chain:

```txt
DTO validation
processor
write service
domain service/entity
Doctrine persistence
mapper
HTTP response
```

Write service unit tests are optional and should be reserved for complex isolated rules.

Useful test categories:

```txt
happy path
nullable fields
referenced resources
cross-organization references
business rule violations
idempotent actions
response DTO
persistence verified by a later GET
```

For changes made directly on entities inside a test, flush before making an API call that reloads from the database.

Example:

```php
$event->publish();

static::getContainer()
    ->get(EntityManagerInterface::class)
    ->flush();
```

## Commit guidance

Commit write features by vertical slice when possible.

Examples:

```bash
git commit -m "Add event write API and status transitions"
git commit -m "Add event registration collection endpoint"
git commit -m "Add event registration create and cancel endpoints"
git commit -m "Add event registration status flow coverage"
```
