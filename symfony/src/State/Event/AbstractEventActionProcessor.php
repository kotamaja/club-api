<?php

namespace App\State\Event;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Core\Event\Entity\Event;
use App\Dto\Event\EventItemDto;
use App\Entity\ConnectionUser;
use App\Mapper\MapperRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractEventActionProcessor implements ProcessorInterface
{
    public function __construct(protected readonly MapperRegistry $mapperRegistry, protected readonly Security $security)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data === null) {
            throw new NotFoundHttpException();
        }

        if (!$data instanceof Event) {
            throw new \LogicException('Expected Event.');
        }

        $event = $this->processEvent($data, $this->getCurrentConnectionUser(), $context);

        return $this->mapperRegistry->map($event, EventItemDto::class);
    }

    abstract protected function processEvent(Event $event, ConnectionUser $actor, array $context): Event;

    protected function getCurrentConnectionUser(): ConnectionUser
    {
        $user = $this->security->getUser();

        if (!$user instanceof ConnectionUser) {
            throw new \LogicException('Expected authenticated ConnectionUser.');
        }

        return $user;
    }
}
