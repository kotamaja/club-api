<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use ApiPlatform\Doctrine\Orm\Filter\SortFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Dto\PersonContact\PersonContactCreateDto;
use App\Dto\PersonContact\PersonContactPatchDto;
use App\Dto\PersonContact\PersonContactViewDto;
use App\Enum\RelationshipType;
use App\Repository\PersonContactRepository;
use App\State\CollectionProvider;
use App\State\ItemProvider;
use App\State\PersonContact\PersonContactCreateProcessor;
use App\State\PersonContact\PersonContactDeleteProcessor;
use App\State\PersonContact\PersonContactPatchProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(operations: [
    new GetCollection(
        uriTemplate: '/person_contacts',
        output: PersonContactViewDto::class,
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
            'personId' => new QueryParameter(
                schema: [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'uniqueItems' => true,
                ],
                filter: new ExactFilter(),
                property: 'person.publicId',
                constraints: [
                    new Assert\All([
                        new Assert\NotBlank(),
                        new Assert\Ulid(),
                    ]),
                ],
                castToArray: true,
            ),
            'contactPersonId' => new QueryParameter(
                schema: [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'uniqueItems' => true,
                ],
                filter: new ExactFilter(),
                property: 'contactPerson.publicId',
                constraints: [
                    new Assert\All([
                        new Assert\NotBlank(),
                        new Assert\Ulid(),
                    ]),
                ],
                castToArray: true,
            ),
            'type' => new QueryParameter(
                filter: new ExactFilter(),
                property: 'type',
            ),
            'isEmergencyContact' => new QueryParameter(
                filter: new BooleanFilter(),
                property: 'isEmergencyContact',
            ),

            // Tri : un paramètre par propriété
            'orderId' => new QueryParameter(
                filter: new SortFilter(),
                property: 'publicId',
            ),
            'orderType' => new QueryParameter(
                filter: new SortFilter(),
                property: 'type',
            ),
            'orderPersonId' => new QueryParameter(
                filter: new SortFilter(),
                property: 'person.publicId',
            ),
            'orderContactPersonId' => new QueryParameter(
                filter: new SortFilter(),
                property: 'contactPerson.publicId',
            ),
        ],
    ),
    new Get(
        uriTemplate: '/person_contacts/{id}',
        uriVariables: [
            'id' => new Link(fromClass: PersonContact::class, identifiers: ['publicId']),
        ],
        output: PersonContactViewDto::class,
        provider: ItemProvider::class,
    ),
    new Post(
        uriTemplate: '/person_contacts',
        input: PersonContactCreateDto::class,
        output: PersonContactViewDto::class,
        processor: PersonContactCreateProcessor::class,
    ),
    new Patch(
        uriTemplate: '/person_contacts/{id}',
        uriVariables: [
            'id' => new Link(fromClass: PersonContact::class, identifiers: ['publicId']),
        ],
        input: PersonContactPatchDto::class,
        output: PersonContactViewDto::class,
        read: true,
        processor: PersonContactPatchProcessor::class,
    ),
    new Delete(
        uriTemplate: '/person_contacts/{id}',
        uriVariables: [
            'id' => new Link(fromClass: PersonContact::class, identifiers: ['publicId']),
        ],
        read: true,
        processor: PersonContactDeleteProcessor::class,
    ),

],
    routePrefix: '/v1',)]
#[ORM\Table(name: 'person_contact')]
#[ORM\Entity(repositoryClass: PersonContactRepository::class)]
#[ORM\Index(name: 'idx_person_contact_subject', columns: ['person_id'])]
#[ORM\Index(name: 'idx_person_contact_related', columns: ['contact_person_id'])]
#[ORM\Index(name: 'idx_person_contact_subject_type', columns: ['person_id', 'type'])]
#[ORM\UniqueConstraint(name: 'uniq_person_contact', columns: ['person_id', 'contact_person_id', 'type'])]
#[Assert\Expression(
    "this.getPerson() !== this.getContactPerson()",
    message: "A person cannot be related to themselves."
)]
class PersonContact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    #[ORM\Column(name: 'public_id', type: 'string', length: 26, unique: true, nullable: false)]
    #[ApiProperty(identifier: true)]
    private string $publicId;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'relationshipsAsPerson')]
    #[ORM\JoinColumn(name: "person_id", referencedColumnName: "id", nullable: false)]
    private ?Person $person = null;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'relationshipsAsContactPerson')]
    #[ORM\JoinColumn(name: "contact_person_id", referencedColumnName: "id", nullable: false)]
    private ?Person $contactPerson = null;

    #[ORM\Column(name: 'type', enumType: RelationshipType::class)]
    private ?RelationshipType $type = null;

    #[ORM\Column(name: 'is_emergency_contact', type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isEmergencyContact = false;

    public function __construct()
    {
        $this->publicId = (string)new Ulid();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): static
    {
        $this->person = $person;

        return $this;
    }

    public function getContactPerson(): ?Person
    {
        return $this->contactPerson;
    }

    public function setContactPerson(?Person $contactPerson): static
    {
        $this->contactPerson = $contactPerson;

        return $this;
    }

    public function getType(): RelationshipType
    {
        return $this->type;
    }

    public function setType(RelationshipType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function isEmergencyContact(): bool
    {
        return $this->isEmergencyContact;
    }

    public function setIsEmergencyContact(bool $isEmergencyContact): static
    {
        $this->isEmergencyContact = $isEmergencyContact;

        return $this;
    }


}
