<?php


namespace App\Events;


use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
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
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var TokenGenerator
     */
    private $tokenGenerator;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(
        UserRepository $userRepository, 
        TokenGenerator $tokenGenerator, 
        EntityManagerInterface $em,
        Mailer $mailer,
        ParameterBagInterface $parameterBag
    )
    {
        $this->userRepository = $userRepository;
        $this->tokenGenerator = $tokenGenerator;
        $this->em = $em;
        $this->mailer = $mailer;
        $this->parameterBag = $parameterBag;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['confirmUser', EventPriorities::POST_VALIDATE]
        ];
    }

    public function confirmUser(ViewEvent $event)
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