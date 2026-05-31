<?php

namespace App\Security\OrganizationContext;

use App\Entity\ConnectionUser;
use App\Entity\Organization;
use App\Entity\OrganizationUser;
use App\Entity\Person;
use App\Enum\UserStatus;
use App\Repository\OrganizationRepository;
use App\Repository\OrganizationUserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class CurrentOrganizationContext
{
    private ?ResolvedOrganizationContext $resolved = null;

    public function __construct(private readonly RequestStack               $requestStack,
                                private readonly Security                   $security,
                                private readonly OrganizationRepository     $organizationRepository,
                                private readonly OrganizationUserRepository $organizationUserRepository,
    )
    {
    }

    public function getOrganization(): Organization
    {
        return $this->resolve()->organization;
    }

    public function getOrganizationUser(): OrganizationUser
    {
        return $this->resolve()->organizationUser;
    }

    public function getPerson(): ?Person
    {
        return $this->resolve()->organizationUser->getPerson();
    }

    private function resolve(): ResolvedOrganizationContext
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            throw new BadRequestHttpException('No current request available.');
        }

        $organizationPublicId = $request->headers->get(OrganizationHeader::NAME);

        if ($organizationPublicId === null || trim($organizationPublicId) === '') {
            throw new BadRequestHttpException(sprintf('Missing %s header.', OrganizationHeader::NAME));
        }

        $user = $this->security->getUser();

        if (!$user instanceof ConnectionUser) {
            throw new AccessDeniedHttpException('Authenticated ConnectionUser expected.');
        }

        if ($user->getStatus() !== UserStatus::Active) {
            throw new AccessDeniedHttpException('ConnectionUser is not active.');
        }

        $organization = $this->organizationRepository->findOneByPublicId($organizationPublicId);

        if ($organization === null) {
            throw new AccessDeniedHttpException('Invalid organization context.');
        }

        if (!$organization->isEnabled()) {
            throw new AccessDeniedHttpException('Invalid organization context.');
        }

        $organizationUser = $this->organizationUserRepository->findOneActiveByConnectionUserAndOrganization($user, $organization);

        if ($organizationUser === null) {
            throw new AccessDeniedHttpException('Invalid organization context.');
        }

        $this->resolved = new ResolvedOrganizationContext(organization: $organization, organizationUser: $organizationUser,);

        return $this->resolved;
    }
}
