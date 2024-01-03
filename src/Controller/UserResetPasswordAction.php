<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserResetPasswordAction
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function __invoke(User $data, Request $request): User
    {
        $token = $request->get('_route_params')['token'];

        $user = $this->userRepository->findOneBy(['resetPasswordToken' => $token]);
        if (!$user) {
            throw new NotFoundHttpException('L\'utilisateur n\'a pas été trouvé.');
        }

        $now = new \DateTime();
        $resetPasswordGeneratedAt = $user->getResetPasswordGeneratedAt();

        if ($resetPasswordGeneratedAt->getTimestamp() + 900 < $now->getTimestamp()) {
            throw new NotFoundHttpException('Le lien de réinitialisation du mot de passe a expiré, veuillez en redemander un nouveau.');
        }

        $newPassword = $data->getPlainPassword();
        $user->setPlainPassword($newPassword);
        $user->setPasswordConfirm($data->getPasswordConfirm());
        $user->setResetPasswordToken(null);
        $user->setResetPasswordGeneratedAt(null);

        return $user;
    }
}
