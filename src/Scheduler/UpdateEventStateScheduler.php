<?php
namespace App\Scheduler;



use App\Message\UpdateEventStateMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

class UpdateEventStateScheduler implements ScheduleProviderInterface
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function getSchedule(): Schedule
    {
        // Planifie la commande toutes les heures, par exemple
        return (new Schedule())->add(
            RecurringMessage::every('1 minute', new UpdateEventStateMessage())
        );
    }
}
