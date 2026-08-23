<?php

namespace App\Tests\Api\Me;

use App\Factory\ClubFactory;
use App\Factory\MembershipFactory;
use App\Factory\OrganizationFactory;
use App\Factory\PersonFactory;
use App\Tests\ApiTestCase;
use DateTimeImmutable;

final class MeClubGetCollectionTest extends ApiTestCase
{
    public function testReturnsAccessibleClubsForOrganization(): void
    {
        $context = $this->createAuthenticatedOrganizationContext(
            includePerson: true,
        );

        $clubB = ClubFactory::createOne([
            'name' => 'Club B',
            'organization' => $context->organization,
        ]);

        $clubA = ClubFactory::createOne([
            'name' => 'Club A',
            'organization' => $context->organization,
        ]);

        MembershipFactory::createOne([
            'person' => $context->person,
            'club' => $clubA,
            'joinedAt' => new DateTimeImmutable('-1 year'),
            'endedAt' => null,
        ]);

        MembershipFactory::createOne([
            'person' => $context->person,
            'club' => $clubB,
            'joinedAt' => new DateTimeImmutable('-1 year'),
            'endedAt' => null,
        ]);

        $response = static::createClient()->request(
            'GET',
            sprintf(
                '/api/v1/me/organizations/%s/clubs',
                $context->organization->getPublicId(),
            ),
            [
                'headers' => $this->authenticatedHeaders($context),
            ],
        );

        self::assertResponseIsSuccessful();

        self::assertSame(
            [
                [
                    'clubId' => $clubA->getPublicId(),
                    'clubName' => 'Club A',
                ],
                [
                    'clubId' => $clubB->getPublicId(),
                    'clubName' => 'Club B',
                ],
            ],
            $response->toArray(),
        );
    }

    public function testDoesNotReturnClubWithEndedMembership(): void
    {
        $context = $this->createAuthenticatedOrganizationContext(
            includePerson: true,
        );

        $activeClub = ClubFactory::createOne([
            'name' => 'Active membership',
            'organization' => $context->organization,
        ]);

        $formerClub = ClubFactory::createOne([
            'name' => 'Former membership',
            'organization' => $context->organization,
        ]);

        MembershipFactory::createOne([
            'person' => $context->person,
            'club' => $activeClub,
            'joinedAt' => new DateTimeImmutable('-1 year'),
            'endedAt' => null,
        ]);

        MembershipFactory::createOne([
            'person' => $context->person,
            'club' => $formerClub,
            'joinedAt' => new DateTimeImmutable('-2 years'),
            'endedAt' => new DateTimeImmutable('-1 year'),
        ]);

        $response = static::createClient()->request(
            'GET',
            sprintf(
                '/api/v1/me/organizations/%s/clubs',
                $context->organization->getPublicId(),
            ),
            [
                'headers' => $this->authenticatedHeaders($context),
            ],
        );

        self::assertResponseIsSuccessful();

        self::assertSame(
            [
                [
                    'clubId' => $activeClub->getPublicId(),
                    'clubName' => 'Active membership',
                ],
            ],
            $response->toArray(),
        );
    }

    public function testReturnsEmptyCollectionWhenOrganizationUserHasNoPerson(): void
    {
        $context = $this->createAuthenticatedOrganizationContext(
            includePerson: false,
        );

        $response = static::createClient()->request(
            'GET',
            sprintf(
                '/api/v1/me/organizations/%s/clubs',
                $context->organization->getPublicId(),
            ),
            [
                'headers' => $this->authenticatedHeaders($context),
            ],
        );

        self::assertResponseIsSuccessful();
        self::assertSame([], $response->toArray());
    }

    public function testDeniesAccessToUnauthorizedOrganization(): void
    {
        $context = $this->createAuthenticatedOrganizationContext();

        $forbiddenOrganization = OrganizationFactory::createOne();

        static::createClient()->request(
            'GET',
            sprintf(
                '/api/v1/me/organizations/%s/clubs',
                $forbiddenOrganization->getPublicId(),
            ),
            [
                'headers' => $this->authenticatedHeaders($context),
            ],
        );

        self::assertResponseStatusCodeSame(403);
    }


    public function testDoesNotReturnClubsFromAnotherOrganization(): void
    {
        $context = $this->createAuthenticatedOrganizationContext(
            includePerson: true,
        );

        $accessibleClub = ClubFactory::createOne([
            'name' => 'Accessible Club',
            'organization' => $context->organization,
        ]);

        MembershipFactory::createOne([
            'person' => $context->person,
            'club' => $accessibleClub,
            'joinedAt' => new DateTimeImmutable('-1 year'),
            'endedAt' => null,
        ]);

        $otherOrganization = OrganizationFactory::createOne();

        $otherPerson = PersonFactory::createOne([
            'organization' => $otherOrganization,
        ]);

        $otherClub = ClubFactory::createOne([
            'name' => 'Other Organization Club',
            'organization' => $otherOrganization,
        ]);

        MembershipFactory::createOne([
            'person' => $otherPerson,
            'club' => $otherClub,
            'joinedAt' => new DateTimeImmutable('-1 year'),
            'endedAt' => null,
        ]);

        $response = static::createClient()->request(
            'GET',
            sprintf(
                '/api/v1/me/organizations/%s/clubs',
                $context->organization->getPublicId(),
            ),
            [
                'headers' => $this->authenticatedHeaders($context),
            ],
        );

        self::assertResponseIsSuccessful();

        self::assertSame(
            [
                [
                    'clubId' => $accessibleClub->getPublicId(),
                    'clubName' => 'Accessible Club',
                ],
            ],
            $response->toArray(),
        );
    }

}
