<?php

namespace App\Write\PublicEventRegistrationRequest;

use App\Core\Event\Entity\PublicEventRegistrationRequest;
use App\Entity\ConnectionUser;

interface PublicEventRegistrationRequestReviewServiceInterface
{
    /**
     * Accepts a pending public request and turns it into a Person and an EventRegistration.
     */
    public function accept(PublicEventRegistrationRequest $request, ConnectionUser $actor): PublicEventRegistrationRequest;

    /**
     * Rejects a pending public request without creating a Person or EventRegistration.
     */
    public function reject(PublicEventRegistrationRequest $request, ?string $reason, ConnectionUser $actor): PublicEventRegistrationRequest;
}
