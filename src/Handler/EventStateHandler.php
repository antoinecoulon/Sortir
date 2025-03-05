<?php
namespace App\Handler;

use App\Command\UpdateEventStateCommand;
use App\Service\EventService;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Console\Tester\CommandTester;

#[AsMessageHandler]
class EventStateHandler implements MessageHandlerInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke($message)
    {
        $eventService = $this->container->get(EventService::class);
        $logger = $this->container->get(LoggerInterface::class);

        $command = new UpdateEventStateCommand($eventService, $logger);
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);
    }
}
