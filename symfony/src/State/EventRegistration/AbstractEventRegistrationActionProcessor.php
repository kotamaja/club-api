<?php

namespace App\State\EventRegistration;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Core\Event\Entity\EventRegistration;
use App\Dto\EventRegistration\EventRegistrationListDto;
use App\Entity\ConnectionUser;
use App\Mapper\MapperRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractEventRegistrationActionProcessor implements ProcessorInterface
{
    public function __construct(protected readonly MapperRegistry         $mapperRegistry,
                                protected readonly EntityManagerInterface $em,
                                protected readonly Security               $security)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data === null) {
            throw new NotFoundHttpException();
        }

        if (!$data instanceof EventRegistration) {
            throw new \LogicException('Expected EventRegistration.');
        }

        $registration = $this->processRegistration($data, $this->getCurrentConnectionUser(), $context);

        $this->em->flush();

        return $this->mapperRegistry->map($registration, EventRegistrationListDto::class);
    }

    abstract protected function processRegistration(EventRegistration $registration, ConnectionUser $actor, array $context): EventRegistration;

    protected function getCurrentConnectionUser(): ConnectionUser
    {
        $user = $this->security->getUser();

        if (!$user instanceof ConnectionUser) {
            throw new \LogicException('Expected authenticated ConnectionUser.');
        }

        return $user;
    }
}
