<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use ApiPlatform\Doctrine\Orm\Filter\PartialSearchFilter;
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
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/people',
            output: PersonListDto::class,
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
                'firstname' => new QueryParameter(
                    filter: new PartialSearchFilter(),
                    property: 'firstname',
                ),
                'lastname' => new QueryParameter(
                    filter: new PartialSearchFilter(),
                    property: 'lastname',
                ),
                'email' => new QueryParameter(
                    filter: new PartialSearchFilter(),
                    property: 'email',
                ),
                'orderId' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'publicId',
                ),
                'orderFirstname' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'firstname',
                ),
                'orderLastname' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'lastname',
                ),
                'orderEmail' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'email',
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
     * @var Collection<int, PersonContact>
     */
    #[ORM\OneToMany(targetEntity: PersonContact::class, mappedBy: 'person')]
    private Collection $relationshipsAsPerson;

    /**
     * @var Collection<int, PersonContact>
     */
    #[ORM\OneToMany(targetEntity: PersonContact::class, mappedBy: 'contactPerson')]
    private Collection $relationshipsAsContactPerson;

    /**
     * @var Collection<int, Membership>
     */
    #[ORM\OneToMany(targetEntity: Membership::class, mappedBy: 'person')]
    private Collection $memberships;

    public function __construct()
    {
        $this->publicId = (string) new Ulid();
        $this->relationshipsAsPerson = new ArrayCollection();
        $this->relationshipsAsContactPerson = new ArrayCollection();
        $this->memberships = new ArrayCollection();
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
     * @return Collection<int, PersonContact>
     */
    public function getRelationshipsAsPerson(): Collection
    {
        return $this->relationshipsAsPerson;
    }

    public function addRelationshipsAsPerson(PersonContact $relationshipsAsPerson): static
    {
        if (!$this->relationshipsAsPerson->contains($relationshipsAsPerson)) {
            $this->relationshipsAsPerson->add($relationshipsAsPerson);
            $relationshipsAsPerson->setPerson($this);
        }

        return $this;
    }

    public function removeRelationshipsAsPerson(PersonContact $relationshipsAsPerson): static
    {
        if ($this->relationshipsAsPerson->removeElement($relationshipsAsPerson)) {
            // set the owning side to null (unless already changed)
            if ($relationshipsAsPerson->getPerson() === $this) {
                $relationshipsAsPerson->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PersonContact>
     */
    public function getRelationshipsAsContactPerson(): Collection
    {
        return $this->relationshipsAsContactPerson;
    }

    public function addRelationshipsAsContactPerson(PersonContact $relationshipsAsContactPerson): static
    {
        if (!$this->relationshipsAsContactPerson->contains($relationshipsAsContactPerson)) {
            $this->relationshipsAsContactPerson->add($relationshipsAsContactPerson);
            $relationshipsAsContactPerson->setContactPerson($this);
        }

        return $this;
    }

    public function removeRelationshipsAsContactPerson(PersonContact $relationshipsAsContactPerson): static
    {
        if ($this->relationshipsAsContactPerson->removeElement($relationshipsAsContactPerson)) {
            // set the owning side to null (unless already changed)
            if ($relationshipsAsContactPerson->getContactPerson() === $this) {
                $relationshipsAsContactPerson->setContactPerson(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, Membership>
     */
    public function getMemberships(): Collection
    {
        return $this->memberships;
    }

    public function addMembership(Membership $membership): static
    {
        if (!$this->memberships->contains($membership)) {
            $this->memberships->add($membership);
            $membership->setPerson($this);
        }

        return $this;
    }

    public function removeMembership(Membership $membership): static
    {
        if ($this->memberships->removeElement($membership)) {
            // set the owning side to null (unless already changed)
            if ($membership->getPerson() === $this) {
                $membership->setPerson(null);
            }
        }

        return $this;
    }

}
