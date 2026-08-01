<?php

namespace App\Write\PublicEventRegistrationRequest;

use App\Core\Event\Contract\Registration\PublicEventRegistrationRequestEligibilityPolicyInterface;
use App\Core\Event\Entity\Event;
use App\Core\Event\Entity\PublicEventRegistrationRequest;
use App\Core\Event\Exception\EventCapacityExceededException;
use App\Core\Event\Exception\EventRegistrationNotAllowedException;
use App\Core\Event\Exception\PublicEventRegistrationRequestRejectedException;
use App\Dto\EventRegistrationRequest\PublicEventRegistrationRequestCreateDto;
use App\Write\Exception\BusinessRuleViolationException;
use Doctrine\ORM\EntityManagerInterface;

final readonly class PublicEventRegistrationRequestWriteService implements PublicEventRegistrationRequestWriteServiceInterface
{
    public function __construct(private PublicEventRegistrationRequestEligibilityPolicyInterface $eligibilityPolicy,
                                private EntityManagerInterface                                  $em)
    {
    }

    /**
     * Creates a pending public registration request after applying public-facing safeguards.
     *
     * This method intentionally creates only a request. It must not create a Person
     * or an EventRegistration directly from unauthenticated input.
     */
    public function create(PublicEventRegistrationRequestCreateDto $input, Event $event): PublicEventRegistrationRequest
    {
        $now = new \DateTimeImmutable();
        $email = $this->normalizeEmail($input->email);

        if ($this->isHoneypotFilled($input)) {
            throw new BusinessRuleViolationException(
                'Registration request cannot be submitted.',
                null,
            );
        }

        try {
            $this->eligibilityPolicy->assertCanSubmit($event, $email, $now);
        } catch (EventCapacityExceededException $e) {
            throw new BusinessRuleViolationException(
                'Public registration requests are not open for this event.',
                'eventId',
                $e,
            );
        } catch (EventRegistrationNotAllowedException $e) {
            throw new BusinessRuleViolationException(
                'Public registration requests are not open for this event.',
                'eventId',
                $e,
            );
        } catch (PublicEventRegistrationRequestRejectedException $e) {
            throw new BusinessRuleViolationException(
                'Registration request cannot be submitted for this email.',
                'email',
                $e,
            );
        }

        $request = PublicEventRegistrationRequest::create(
            event: $event,
            firstname: (string) $input->firstname,
            lastname: (string) $input->lastname,
            email: $email,
            note: $input->note,
            now: $now,
        );

        $this->em->persist($request);

        return $request;
    }

    /**
     * Normalizes public email input before duplicate and eligibility checks.
     */
    private function normalizeEmail(?string $email): string
    {
        return mb_strtolower(trim((string) $email));
    }

    /**
     * Detects basic bots that blindly fill hidden form fields.
     */
    private function isHoneypotFilled(PublicEventRegistrationRequestCreateDto $input): bool
    {
        return trim((string) $input->homepage) !== '';
    }
}
