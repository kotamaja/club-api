<?php

namespace App\Write\Person;

use App\Dto\Person\PersonCreateDto;
use App\Dto\Person\PersonPatchDto;
use App\Entity\ConnectionUser;
use App\Entity\Person;
use App\Repository\MembershipRepository;
use App\Repository\PersonContactRepository;
use App\Security\OrganizationContext\CurrentOrganizationContext;
use App\Security\OrganizationContext\OrganizationScopeGuard;
use App\Write\Exception\BusinessRuleViolationException;
use Doctrine\ORM\EntityManagerInterface;

class PersonWriteService implements PersonWriteServiceInterface
{

    public function __construct(private CurrentOrganizationContext $currentOrganizationContext,
                                private OrganizationScopeGuard     $organizationScopeGuard,
                                private PersonPermissionChecker    $personPermissionChecker,
                                private EntityManagerInterface     $entityManager,
                                private MembershipRepository       $membershipRepository,
                                private PersonContactRepository    $personContactRepository,
    )
    {
    }

    public function create(PersonCreateDto $input, ConnectionUser $actor): Person
    {
        $organization = $this->currentOrganizationContext->getOrganization();

        $this->personPermissionChecker->assertCanCreate($actor, $organization);

        $club = Person::create(firstname: $input->firstname, lastname: $input->lastname, email: $input->email, organization: $organization);

        $this->applyCreateData($input, $club);

        $this->entityManager->persist($club);

        return $club;
    }

    public function patch(PersonPatchDto $input, Person $person, ConnectionUser $actor): PersonPatchResult
    {
        $changed = false;

        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($person);

        $this->personPermissionChecker->assertCanUpdate($actor, $person);

        $changed = $this->applyPatchData($input, $person) || $changed;

        return new PersonPatchResult($person, $changed);
    }

    public function delete(Person $person, ConnectionUser $actor): void
    {

        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($person);

        $this->personPermissionChecker->assertCanDelete($actor, $person);

        if (0 < $this->membershipRepository->count(['person' => $person])) {
            throw new BusinessRuleViolationException(
                'This person cannot be deleted because it is still used by one or more membership.'
            );
        }

        if (0 < $this->personContactRepository->count(['person' => $person])) {
            throw new BusinessRuleViolationException(
                'This person cannot be deleted because it is still used by one or more relationships as person.'
            );
        }

        if (0 < $this->personContactRepository->count(['contactPerson' => $person])) {
            throw new BusinessRuleViolationException(
                'This person cannot be deleted because it is still used by one or more relationships as contact person.'
            );
        }

        $this->entityManager->remove($person);

    }

    private function applyCreateData(PersonCreateDto $input, Person $person): void
    {
        // nothing yet
    }

    private function applyPatchData(PersonPatchDto $input, Person $person): bool
    {
        $changed = false;

        $changed = $this->applyFirstName($input, $person) || $changed;
        $changed = $this->applyLastName($input, $person) || $changed;
        $changed = $this->applyEmail($input, $person) || $changed;

        return $changed;
    }

    private function applyFirstName(PersonPatchDto $input, Person $person): bool
    {
        if (!$input->isFirstnameProvided()) {
            return false;
        }

        $firstname = $input->getFirstname();

        if ($firstname === $person->getFirstname()) {
            return false;
        }

        $person->setFirstname($firstname);

        return true;
    }

    private function applyLastName(PersonPatchDto $input, Person $person): bool
    {
        if (!$input->isLastnameProvided()) {
            return false;
        }

        $lastname = $input->getLastname();

        if ($lastname === $person->getLastname()) {
            return false;
        }

        $person->setLastname($lastname);

        return true;
    }

    private function applyEmail(PersonPatchDto $input, Person $person): bool
    {
        if (!$input->isEmailProvided()) {
            return false;
        }

        $email = $input->getEmail();

        if ($email === $person->getEmail()) {
            return false;
        }

        $person->setEmail($email);

        return true;
    }
}
