<?php

namespace App\Services;

use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class Mailer
{
    public function __construct(private readonly MailerInterface $mailer, private readonly Environment $templating
    ) {
    }

    public function sendResetPasswordRequestEmail(string $from, string $to, string $user, string $subject, string $view): void
    {
        $email = new Email();
        $email
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->html($this->templating->render($view, ['user' => $user]));

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface) {
            throw new TransportException('Une erreur est survenue');
        }
    }
}
