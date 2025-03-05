<?php

namespace App\Notification;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\User\UserInterface;

class Sender
{
    private readonly MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendMailRegister(UserInterface $user, $event): void
    {
        try {
            $message = new Email();
            $message->from('sortir@eni.com')
                ->to($user->getEmail())
                ->subject("Inscription à l'évenement {$event->getName()} ")
            ->html("<h1>Bonjour {$user->getFirstname()}</h1>
             <div>Vous êtes bien inscrit à l\'évenement {$event->getName()}");
            $this->mailer->send($message);
        } catch(\Exception $e) {
            dump($e);
        }
    }

    public function sendMailUnregister(UserInterface $user, $event): void
    {
        try {
            $message = new Email();
            $message->from('sortir@eni.com')
                ->to($user->getEmail())
                ->subject("Désinscription à l'évenement {$event->getName()} ")
                ->html("<h1>Bonjour {$user->getFirstname()}</h1>
             <div>Vous êtes bien désinscrit à l\'évenement {$event->getName()}");
            $this->mailer->send($message);
        } catch(\Exception $e) {
            dump($e);
        }
    }
}