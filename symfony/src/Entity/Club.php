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
use App\Dto\Club\ClubCreateDto;
use App\Dto\Club\ClubItemDto;
use App\Dto\Club\ClubListDto;
use App\Dto\Club\ClubPatchDto;
use App\Repository\ClubRepository;
use App\State\Club\ClubCreateProcessor;
use App\State\Club\ClubDeleteProcessor;
use App\State\Club\ClubPatchProcessor;
use App\State\CollectionProvider;
use App\State\ItemProvider;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/clubs',
            output: ClubListDto::class,
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
                'name' => new QueryParameter(
                    filter: new PartialSearchFilter(),
                    property: 'name',
                ),
                'orderId' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'publicId',
                ),
                'orderName' => new QueryParameter(
                    filter: new SortFilter(),
                    property: 'name',
                ),
            ],
        ),
        new Get(
            uriTemplate: '/clubs/{id}',
            uriVariables: [
                'id' => new Link(fromClass: Club::class, identifiers: ['publicId']),
            ],
            output: ClubItemDto::class,
            provider: ItemProvider::class,
        ),
        new Post(
            uriTemplate: '/clubs',
            input: ClubCreateDto::class,
            output: ClubItemDto::class,
            processor: ClubCreateProcessor::class,
        ),
        new Patch(
            uriTemplate: '/clubs/{id}',
            uriVariables: [
                'id' => new Link(fromClass: Club::class, identifiers: ['publicId']),
            ],
            input: ClubPatchDto::class,
            output: ClubItemDto::class,
            read: true,
            processor: ClubPatchProcessor::class,
        ),
        new Delete(
            uriTemplate: '/clubs/{id}',
            uriVariables: [
                'id' => new Link(fromClass: Club::class, identifiers: ['publicId']),
            ],
            read: true,
            processor: ClubDeleteProcessor::class,
        ),
    ],
    routePrefix: '/v1',
)]

#[ORM\Table(name: 'club')]
#[ORM\Entity(repositoryClass: ClubRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_club_public_id', columns: ['public_id'])]
class Club
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    #[ORM\Column(name: 'public_id', type: 'string', length: 26, unique: true, nullable: false)]
    #[ApiProperty(identifier: true)]
    private string $publicId;


    #[ORM\Column(name: 'name',  type: Types::STRING, length: 150)]
    private ?string $name = null;



    public function __construct()
    {
        $this->publicId = (string) new Ulid();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }




}
