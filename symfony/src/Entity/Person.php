<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\PartialSearchFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Dto\Person\PersonCreateDto;
use App\Dto\Person\PersonItemDto;
use App\Dto\Person\PersonListDto;
use App\Dto\Person\PersonPatchDto;
use App\Repository\PersonRepository;
use App\State\CollectionProvider;
use App\State\ItemProvider;
use App\State\Person\PersonCreateProcessor;
use App\State\Person\PersonDeleteProcessor;
use App\State\Person\PersonPatchProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/people',
            output: PersonListDto::class,
            provider: CollectionProvider::class,
            parameters: [
                // Filtres (API Platform 4 : QueryParameter + Filter)
                'firstname' => new QueryParameter(filter: new PartialSearchFilter()),
                'lastname' => new QueryParameter(filter: new PartialSearchFilter()),
                'email' => new QueryParameter(filter: new PartialSearchFilter()),
                // ou en SearchFilter "partial" via ApiFilter (voir plus bas)
                'order[:property]' => new QueryParameter(
                    filter: new OrderFilter(),
                    properties: ['firstname', 'lastname','email', 'publicId'],
                ),
            ],
        ),
        new Get(
            uriTemplate: '/people/{id}',
            uriVariables: [
                'id' => new Link(fromClass: Person::class, identifiers: ['publicId']),
            ],
            output: PersonItemDto::class,
            provider: ItemProvider::class,
        ),
        new Post(
            uriTemplate: '/people',
            input: PersonCreateDto::class,
            output: PersonItemDto::class,
            processor: PersonCreateProcessor::class,
        ),
        new Patch(
            uriTemplate: '/people/{id}',
            uriVariables: [
                'id' => new Link(fromClass: Person::class, identifiers: ['publicId']),
            ],
            input: PersonPatchDto::class,
            output: PersonItemDto::class,
            read: true,
            processor: PersonPatchProcessor::class,
        ),
        new Delete(
            uriTemplate: '/people/{id}',
            uriVariables: [
                'id' => new Link(fromClass: Person::class, identifiers: ['publicId']),
            ],
            read: true,
            processor: PersonDeleteProcessor::class,
        ),
    ],
    routePrefix: '/v1',
)]

#[ORM\Table(name: 'person')]
#[ORM\Entity(repositoryClass: PersonRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_person_public_id', columns: ['public_id'])]
class Person
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    #[ORM\Column(name: 'public_id', type: 'string', length: 26, unique: true, nullable: false)]
    #[ApiProperty(identifier: true)]
    private string $publicId;


    #[ORM\Column(name: 'firstname',  type: Types::STRING, length: 150)]
    private ?string $firstname = null;

    #[ORM\Column(name: 'lastname',  type: Types::STRING, length: 150)]
    private ?string $lastname = null;

    #[ORM\Column(name: 'email',  type: Types::STRING, length: 180)]
    private ?string $email = null;

    /**
     * @var Collection<int, PersonRelationship>
     */
    #[ORM\OneToMany(targetEntity: PersonRelationship::class, mappedBy: 'subject')]
    private Collection $relationshipsAsSubject;

    /**
     * @var Collection<int, PersonRelationship>
     */
    #[ORM\OneToMany(targetEntity: PersonRelationship::class, mappedBy: 'relatedPerson')]
    private Collection $relationshipsAsRelatedPerson;


    public function __construct()
    {
        $this->publicId = (string) new Ulid();
        $this->relationshipsAsSubject = new ArrayCollection();
        $this->relationshipsAsRelatedPerson = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, PersonRelationship>
     */
    public function getRelationshipsAsSubject(): Collection
    {
        return $this->relationshipsAsSubject;
    }

    public function addRelationshipsAsSubject(PersonRelationship $relationshipsAsSubject): static
    {
        if (!$this->relationshipsAsSubject->contains($relationshipsAsSubject)) {
            $this->relationshipsAsSubject->add($relationshipsAsSubject);
            $relationshipsAsSubject->setSubject($this);
        }

        return $this;
    }

    public function removeRelationshipsAsSubject(PersonRelationship $relationshipsAsSubject): static
    {
        if ($this->relationshipsAsSubject->removeElement($relationshipsAsSubject)) {
            // set the owning side to null (unless already changed)
            if ($relationshipsAsSubject->getSubject() === $this) {
                $relationshipsAsSubject->setSubject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PersonRelationship>
     */
    public function getRelationshipsAsRelatedPerson(): Collection
    {
        return $this->relationshipsAsRelatedPerson;
    }

    public function addRelationshipsAsRelatedPerson(PersonRelationship $relationshipsAsRelatedPerson): static
    {
        if (!$this->relationshipsAsRelatedPerson->contains($relationshipsAsRelatedPerson)) {
            $this->relationshipsAsRelatedPerson->add($relationshipsAsRelatedPerson);
            $relationshipsAsRelatedPerson->setRelatedPerson($this);
        }

        return $this;
    }

    public function removeRelationshipsAsRelatedPerson(PersonRelationship $relationshipsAsRelatedPerson): static
    {
        if ($this->relationshipsAsRelatedPerson->removeElement($relationshipsAsRelatedPerson)) {
            // set the owning side to null (unless already changed)
            if ($relationshipsAsRelatedPerson->getRelatedPerson() === $this) {
                $relationshipsAsRelatedPerson->setRelatedPerson(null);
            }
        }

        return $this;
    }
}
