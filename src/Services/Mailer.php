<?php


namespace App\Services;

use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class Mailer
{
    protected $mailer;

    private $templating;

    public function __construct(MailerInterface $mailer, Environment $templating
    )
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    public function sendResetPasswordRequestEmail($from, $to, $user, $subject, $view)
    {
        $email = new Email();
        $email
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->html($this->templating->render($view, ['user' => $user]));

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throw new TransportException('Une erreur est survenue');
        }
    }
}