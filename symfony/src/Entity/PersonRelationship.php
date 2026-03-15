<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\PartialSearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Dto\PersonRelationship\PersonRelationshipCreateDto;
use App\Dto\PersonRelationship\PersonRelationshipPatchDto;
use App\Dto\PersonRelationship\PersonRelationshipViewDto;
use App\Enum\RelationshipType;
use App\Repository\PersonRelationShipRepository;
use App\State\CollectionProvider;
use App\State\ItemProvider;
use App\State\PersonRelationship\PersonRelationshipCreateProcessor;
use App\State\PersonRelationship\PersonRelationshipDeleteProcessor;
use App\State\PersonRelationship\PersonRelationshipPatchProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(operations: [
    new GetCollection(
        uriTemplate: '/person_relationships',
        output: PersonRelationshipViewDto::class,
        provider: CollectionProvider::class,
        parameters: [
            // Filtres (API Platform 4 : QueryParameter + Filter)
            'subjectId' => new QueryParameter(filter: new PartialSearchFilter()),
            'relatedPersonId' => new QueryParameter(filter: new PartialSearchFilter()),
            'type' => new QueryParameter(filter: new PartialSearchFilter()),
            // ou en SearchFilter "partial" via ApiFilter (voir plus bas)
            'order[:property]' => new QueryParameter(
                filter: new OrderFilter(),
                properties: ['subject', 'relatedPerson', 'type', 'publicId'],
            ),
        ],
    ),
    new Get(
        uriTemplate: '/person_relationships/{id}',
        output: PersonRelationshipViewDto::class,
        provider: ItemProvider::class,
    ),
    new Post(
        uriTemplate: '/person_relationships',
        input: PersonRelationshipCreateDto::class,
        output: PersonRelationshipViewDto::class,
        processor: PersonRelationshipCreateProcessor::class,
    ),
    new Patch(
        uriTemplate: '/person_relationships/{id}',
        input: PersonRelationshipPatchDto::class,
        output: PersonRelationshipViewDto::class,
        read: true,
        provider: ItemProvider::class,
        processor: PersonRelationshipPatchProcessor::class,
    ),
    new Delete(
        uriTemplate: '/person_relationships/{id}',
        read: true,
        provider: ItemProvider::class,
        processor: PersonRelationshipDeleteProcessor::class,
    ),
])]

//#[ApiFilter(SearchFilter::class, properties: [
//    'subject' => 'exact',
//    'relatedPerson' => 'exact',
//    'type' => 'exact',
//])]

#[ORM\Table(name: 'person_relationship')]
#[ORM\Entity(repositoryClass: PersonRelationShipRepository::class)]
#[ORM\Index(name: 'idx_person_relationship_subject', columns: ['subject_id'])]
#[ORM\Index(name: 'idx_person_relationship_related', columns: ['related_person_id'])]
#[ORM\UniqueConstraint(name: 'uniq_person_relationship', columns: ['subject_id', 'related_person_id', 'type'])]
#[Assert\Expression(
    "this.getSubject() !== this.getRelatedPerson()",
    message: "A person cannot be related to themselves."
)]
class PersonRelationship
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    #[ORM\Column(name: 'public_id', type: 'string', length: 26, unique: true, nullable: false)]
    #[ApiProperty(identifier: true)]
    private string $publicId;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'relationshipsAsSubject')]
    #[ORM\JoinColumn(name: "subject_id", referencedColumnName: "id", nullable: false, onDelete: 'CASCADE')]
    private ?Person $subject = null;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'relationshipsAsRelatedPerson')]
    #[ORM\JoinColumn(name: "related_person_id", referencedColumnName: "id", nullable: false, onDelete: 'CASCADE')]
    private ?Person $relatedPerson = null;

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

    public function getSubject(): ?Person
    {
        return $this->subject;
    }

    public function setSubject(?Person $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getRelatedPerson(): ?Person
    {
        return $this->relatedPerson;
    }

    public function setRelatedPerson(?Person $relatedPerson): static
    {
        $this->relatedPerson = $relatedPerson;

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

    public function setEmergencyContact(bool $isEmergencyContact): static
    {
        $this->isEmergencyContact = $isEmergencyContact;

        return $this;
    }


}
