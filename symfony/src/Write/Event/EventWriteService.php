<?php

namespace App\Write\Event;

use App\Core\Event\Entity\Event;
use App\Core\Event\Enum\EventType;
use App\Core\Event\Repository\EventRegistrationRepository;
use App\Dto\Event\EventCreateDto;
use App\Dto\Event\EventPatchDto;
use App\Entity\Club;
use App\Entity\ConnectionUser;
use App\Repository\ClubRepository;
use App\Security\OrganizationContext\CurrentOrganizationContext;
use App\Security\OrganizationContext\OrganizationScopeGuard;
use App\Write\Exception\BusinessRuleViolationException;
use App\Write\Exception\ReferencedResourceNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

class EventWriteService implements EventWriteServiceInterface
{
    public function __construct(private CurrentOrganizationContext  $currentOrganizationContext,
                                private OrganizationScopeGuard      $organizationScopeGuard,
                                private EventPermissionChecker      $eventPermissionChecker,
                                private EntityManagerInterface      $entityManager,
                                private ClubRepository              $clubRepository,
                                private EventRegistrationRepository $eventRegistrationRepository,
    )
    {
    }

    public function create(EventCreateDto $input, ConnectionUser $actor): Event
    {
        $organization = $this->currentOrganizationContext->getOrganization();

        $this->eventPermissionChecker->assertCanCreate($actor, $organization);

        $this->assertValidDateRange($input->startsAt, $input->endsAt);

        $event = Event::create(
            organization: $organization,
            title: $input->title,
            startsAt: $input->startsAt,
            endsAt: $input->endsAt,
        );

        $this->applyCreateData($input, $event);

        $this->entityManager->persist($event);

        return $event;
    }

    public function patch(EventPatchDto $input, Event $event, ConnectionUser $actor): EventPatchResult
    {
        $changed = false;

        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($event);

        $this->eventPermissionChecker->assertCanUpdate($actor, $event);

        $changed = $this->applyPatchData($input, $event) || $changed;

        return new EventPatchResult($event, $changed);
    }

    public function delete(Event $event, ConnectionUser $actor): void
    {
        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($event);

        $this->eventPermissionChecker->assertCanDelete($actor, $event);

        if (0 < $this->eventRegistrationRepository->count(['event' => $event])) {
            throw new BusinessRuleViolationException(
                'This event cannot be deleted because it already has one or more registrations.'
            );
        }

        $this->entityManager->remove($event);
    }

    public function publish(Event $event, ConnectionUser $actor): Event
    {
        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($event);

        $this->eventPermissionChecker->assertCanPublish($actor, $event);

        $event->publish();

        return $event;
    }

    public function cancel(Event $event, ConnectionUser $actor): Event
    {
        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($event);

        $this->eventPermissionChecker->assertCanCancel($actor, $event);

        $event->cancel();

        return $event;
    }

    public function archive(Event $event, ConnectionUser $actor): Event
    {
        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($event);

        $this->eventPermissionChecker->assertCanArchive($actor, $event);

        $event->archive();

        return $event;
    }

    private function applyCreateData(EventCreateDto $input, Event $event): void
    {
        if ($input->clubId !== null) {
            $event->attachToClub($this->getClub($input->clubId));
        }

        $event->changeType($input->type ?? EventType::General);

        $event->changeDescription($input->description);
        $event->changeLocation($input->location);

        if ($input->timezone !== null) {
            $event->changeTimezone($input->timezone);
        }

        if ($input->allDay) {
            $event->markAsAllDay();
        } else {
            $event->markAsTimed();
        }

        $event->changeCapacity($input->capacity);

        if ($input->waitlistEnabled) {
            $event->enableWaitlist();
        } else {
            $event->disableWaitlist();
        }

        if ($input->publicRegistrationEnabled) {
            $event->enablePublicRegistration();
        } else {
            $event->disablePublicRegistration();
        }

        $event->changeRegistrationWindow(
            $input->registrationStartsAt,
            $input->registrationEndsAt,
        );
    }

    private function applyPatchData(EventPatchDto $input, Event $event): bool
    {
        $changed = false;

        $changed = $this->applyClub($input, $event) || $changed;
        $changed = $this->applyType($input, $event) || $changed;
        $changed = $this->applyTitle($input, $event) || $changed;
        $changed = $this->applyDescription($input, $event) || $changed;
        $changed = $this->applyLocation($input, $event) || $changed;
        $changed = $this->applySchedule($input, $event) || $changed;
        $changed = $this->applyTimezone($input, $event) || $changed;
        $changed = $this->applyAllDay($input, $event) || $changed;
        $changed = $this->applyCapacity($input, $event) || $changed;
        $changed = $this->applyWaitlistEnabled($input, $event) || $changed;
        $changed = $this->applyPublicRegistrationEnabled($input, $event) || $changed;
        $changed = $this->applyRegistrationWindow($input, $event) || $changed;

        return $changed;
    }

    private function applyClub(EventPatchDto $input, Event $event): bool
    {
        if (!$input->isClubIdProvided()) {
            return false;
        }

        $clubId = $input->getClubId();
        $club = $clubId === null ? null : $this->getClub($clubId);

        if ($club === $event->getClub()) {
            return false;
        }

        $event->attachToClub($club);

        return true;
    }

    private function applyType(EventPatchDto $input, Event $event): bool
    {
        if (!$input->isTypeProvided()) {
            return false;
        }

        $type = $input->getType();

        if ($type === null) {
            throw new BusinessRuleViolationException('Type cannot be null.', 'type');
        }

        if ($type === $event->getType()) {
            return false;
        }

        $event->changeType($type);

        return true;
    }

    private function applyTitle(EventPatchDto $input, Event $event): bool
    {
        if (!$input->isTitleProvided()) {
            return false;
        }

        $title = $input->getTitle();

        if ($title === null) {
            throw new BusinessRuleViolationException('Title cannot be null.', 'title');
        }

        if ($title === $event->getTitle()) {
            return false;
        }

        $event->rename($title);

        return true;
    }

    private function applyDescription(EventPatchDto $input, Event $event): bool
    {
        if (!$input->isDescriptionProvided()) {
            return false;
        }

        $description = $input->getDescription();

        if ($description === $event->getDescription()) {
            return false;
        }

        $event->changeDescription($description);

        return true;
    }

    private function applyLocation(EventPatchDto $input, Event $event): bool
    {
        if (!$input->isLocationProvided()) {
            return false;
        }

        $location = $input->getLocation();

        if ($location === $event->getLocation()) {
            return false;
        }

        $event->changeLocation($location);

        return true;
    }

    private function applySchedule(EventPatchDto $input, Event $event): bool
    {
        if (!$input->isStartsAtProvided() && !$input->isEndsAtProvided()) {
            return false;
        }

        $startsAt = $input->isStartsAtProvided()
            ? $input->getStartsAt()
            : $event->getStartsAt();

        $endsAt = $input->isEndsAtProvided()
            ? $input->getEndsAt()
            : $event->getEndsAt();

        $this->assertValidDateRange($startsAt, $endsAt);

        if ($startsAt == $event->getStartsAt() && $endsAt == $event->getEndsAt()) {
            return false;
        }

        $event->reschedule($startsAt, $endsAt);

        return true;
    }

    private function applyTimezone(EventPatchDto $input, Event $event): bool
    {
        if (!$input->isTimezoneProvided()) {
            return false;
        }

        $timezone = $input->getTimezone();

        if ($timezone === null) {
            throw new BusinessRuleViolationException('Timezone cannot be null.', 'timezone');
        }

        if ($timezone === $event->getTimezone()) {
            return false;
        }

        $event->changeTimezone($timezone);

        return true;
    }

    private function applyAllDay(EventPatchDto $input, Event $event): bool
    {
        if (!$input->isAllDayProvided()) {
            return false;
        }

        $allDay = $input->getAllDay();

        if ($allDay === null) {
            throw new BusinessRuleViolationException('All day flag cannot be null.', 'allDay');
        }

        if ($allDay === $event->isAllDay()) {
            return false;
        }

        if ($allDay) {
            $event->markAsAllDay();
        } else {
            $event->markAsTimed();
        }

        return true;
    }

    private function applyCapacity(EventPatchDto $input, Event $event): bool
    {
        if (!$input->isCapacityProvided()) {
            return false;
        }

        $capacity = $input->getCapacity();

        if ($capacity === $event->getCapacity()) {
            return false;
        }

        $event->changeCapacity($capacity);

        return true;
    }

    private function applyWaitlistEnabled(EventPatchDto $input, Event $event): bool
    {
        if (!$input->isWaitlistEnabledProvided()) {
            return false;
        }

        $waitlistEnabled = $input->getWaitlistEnabled();

        if ($waitlistEnabled === null) {
            throw new BusinessRuleViolationException('Waitlist flag cannot be null.', 'waitlistEnabled');
        }

        if ($waitlistEnabled === $event->isWaitlistEnabled()) {
            return false;
        }

        if ($waitlistEnabled) {
            $event->enableWaitlist();
        } else {
            $event->disableWaitlist();
        }

        return true;
    }

    private function applyPublicRegistrationEnabled(EventPatchDto $input, Event $event): bool
    {
        if (!$input->isPublicRegistrationEnabledProvided()) {
            return false;
        }

        $publicRegistrationEnabled = $input->getPublicRegistrationEnabled();

        if ($publicRegistrationEnabled === null) {
            throw new BusinessRuleViolationException('Public registration flag cannot be null.', 'publicRegistrationEnabled');
        }

        if ($publicRegistrationEnabled === $event->isPublicRegistrationEnabled()) {
            return false;
        }

        if ($publicRegistrationEnabled) {
            $event->enablePublicRegistration();
        } else {
            $event->disablePublicRegistration();
        }

        return true;
    }

    private function applyRegistrationWindow(EventPatchDto $input, Event $event): bool
    {
        if (!$input->isRegistrationStartsAtProvided() && !$input->isRegistrationEndsAtProvided()) {
            return false;
        }

        $registrationStartsAt = $input->isRegistrationStartsAtProvided()
            ? $input->getRegistrationStartsAt()
            : $event->getRegistrationStartsAt();

        $registrationEndsAt = $input->isRegistrationEndsAtProvided()
            ? $input->getRegistrationEndsAt()
            : $event->getRegistrationEndsAt();

        if (
            $registrationStartsAt == $event->getRegistrationStartsAt()
            && $registrationEndsAt == $event->getRegistrationEndsAt()
        ) {
            return false;
        }

        $event->changeRegistrationWindow($registrationStartsAt, $registrationEndsAt);

        return true;
    }

    private function getClub(string $publicId): Club
    {
        $club = $this->clubRepository->findOneBy(['publicId' => $publicId]);

        if (!$club instanceof Club) {
            throw new ReferencedResourceNotFoundException('Club not found.', 'clubId');
        }

        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($club);

        return $club;
    }

    private function assertValidDateRange(?\DateTimeImmutable $startsAt, ?\DateTimeImmutable $endsAt): void
    {
        if ($startsAt === null) {
            throw new BusinessRuleViolationException('Start date cannot be null.', 'startsAt');
        }

        if ($endsAt === null) {
            throw new BusinessRuleViolationException('End date cannot be null.', 'endsAt');
        }

        if ($endsAt <= $startsAt) {
            throw new BusinessRuleViolationException(
                'Event end date must be after start date.',
                'endsAt',
            );
        }
    }
}
