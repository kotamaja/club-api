<?php

namespace App\Write\PersonContact;

use App\Dto\PersonContact\PersonContactCreateDto;
use App\Dto\PersonContact\PersonContactPatchDto;
use App\Entity\ConnectionUser;
use App\Entity\Person;
use App\Entity\PersonContact;
use App\Security\OrganizationContext\CurrentOrganizationContext;
use App\Security\OrganizationContext\OrganizationScopeGuard;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class PersonContactWriteService implements PersonContactWriteServiceInterface
{

    public function __construct(private CurrentOrganizationContext     $currentOrganizationContext,
                                private OrganizationScopeGuard         $organizationScopeGuard,
                                private PersonContactPermissionChecker $permissionChecker,
                                private PersonContactBusinessRules     $businessRules,
                                private EntityManagerInterface         $entityManager,
    )
    {
    }

    public function create(PersonContactCreateDto $input, ConnectionUser $actor): PersonContact
    {
        $organization = $this->currentOrganizationContext->getOrganization();

        $this->permissionChecker->assertCanCreate($actor, $organization);

        $person = $this->getPerson($input->personId);
        $contactPerson = $this->getPerson($input->contactPersonId);

        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($person);
        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($contactPerson);

        $this->businessRules->assertNotSelfContact($person, $contactPerson);

        $personContact = PersonContact::create(
            person: $person,
            contactPerson: $contactPerson,
            type: $input->type,
            isEmergencyContact: $input->isEmergencyContact,
        );

        $this->entityManager->persist($personContact);

        return $personContact;
    }

    public function patch(PersonContactPatchDto $input, PersonContact $personContact, ConnectionUser $actor): PersonContactPatchResult
    {

        $this->permissionChecker->assertCanUpdate($actor, $personContact);

        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($personContact->getPerson());
        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($personContact->getContactPerson());

        $changed = false;

        if ($input->isTypeProvided() && $input->getType() !== $personContact->getType()) {
            $personContact->changeType($input->getType());
            $changed = true;
        }

        if ($input->isEmergencyContactProvided() && $input->getIsEmergencyContact() !== $personContact->isEmergencyContact()) {
            $personContact->changeEmergencyContact($input->getIsEmergencyContact());
            $changed = true;
        }

        return new PersonContactPatchResult($personContact, $changed);
    }

    public function delete(PersonContact $personContact, ConnectionUser $actor): void
    {

        $this->permissionChecker->assertCanDelete($actor, $personContact);

        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($personContact->getPerson());
        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($personContact->getContactPerson());

        $this->entityManager->remove($personContact);
    }


    private function getPerson(string $publicId): Person
    {

        $person = $this->entityManager->getRepository(Person::class)->findOneBy([
            'publicId' => $publicId,
        ]);

        if (!$person instanceof Person) {
            throw new NotFoundHttpException('Person not found.');
        }

        return $person;
    }


}
