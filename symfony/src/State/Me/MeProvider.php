<?php

namespace App\State\Me;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Me;
use App\Entity\ConnectionUser;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @implements ProviderInterface<Me>
 */
final readonly class MeProvider implements ProviderInterface
{
    public function __construct(
        private Security $security,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Me
    {
        $user = $this->security->getUser();

        if (!$user instanceof ConnectionUser) {
            throw new AccessDeniedHttpException('Authenticated ConnectionUser expected.');
        }

        return new Me(
            id: $user->getPublicId(),
            email: $user->getEmail(),
            status: $user->getStatus()->value,
            roles: $user->getRoles(),
        );
    }
}
