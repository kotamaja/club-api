<?php

namespace App\Security\Authentication;

use App\Entity\ConnectionUser;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

final readonly class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(private JWTTokenManagerInterface $jwtTokenManager, private EntityManagerInterface $entityManager)
    {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $user = $token->getUser();

        if (!$user instanceof ConnectionUser) {
            return new JsonResponse(['message' => 'Invalid authenticated user.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $user->setLastLoginAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        $jwt = $this->jwtTokenManager->create($user);

        return new JsonResponse([
            'token' => $jwt,
        ]);
    }
}
