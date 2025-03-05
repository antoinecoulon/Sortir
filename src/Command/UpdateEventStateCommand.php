<?php

namespace App\Command;

use App\Service\EventService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateEventStateCommand extends Command
{
    private readonly EventService $eventService;
    private LoggerInterface  $logger;

    public function __construct(EventService $eventService, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->eventService = $eventService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:update-event-statuses')
            ->setDescription('Mise à jour du statut des évènements.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->eventService->updateEventState();

        $output->writeln('Les statuts des événements ont été mis à jour !');
        $this->logger->info('Le cron updateEventStatus a terminé sa tache !');
        return Command::SUCCESS;
    }
}
