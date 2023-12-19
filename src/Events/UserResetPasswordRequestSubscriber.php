<?php

namespace App\Events;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Repository\UserRepository;
use App\Security\TokenGenerator;
use App\Services\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class UserResetPasswordRequestSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly UserRepository $userRepository, private readonly TokenGenerator $tokenGenerator, private readonly EntityManagerInterface $em, private readonly Mailer $mailer, private readonly ParameterBagInterface $parameterBag)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['confirmUser', EventPriorities::POST_VALIDATE],
        ];
    }

    public function confirmUser(ViewEvent $event): void
    {
        $request = $event->getRequest();

        if ('api_users_reset_password_request_collection' !== $request->get('_route')) {
            return;
        }

        $email = $event->getControllerResult()->getEmail();
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new NotFoundHttpException('L\'utilisateur n\'a pas été trouvé');
        }

        $token = $this->tokenGenerator->getRandomSecureToken();
        $user->setResetPasswordToken($token);
        $user->setResetPasswordGeneratedAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();

        $from = $this->parameterBag->get('mailer_from');
        $to = $user->getEmail();
        $view = 'email/resetPasswordRequest.html.twig';
        $subject = 'Mot de passe oublié';

        $this->mailer->sendResetPasswordRequestEmail($from, $to, $user, $subject, $view);

        $event->setResponse(new JsonResponse('Un email vous as été envoyé', Response::HTTP_OK));
    }
}
