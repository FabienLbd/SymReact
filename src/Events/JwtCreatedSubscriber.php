<?php


namespace App\Events;


use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JwtCreatedSubscriber
{
    public function updateJwtData(JWTCreatedEvent $event)
    {
        $user = $event->getUser();
        if ($user instanceof User) {
            $data = $event->getData();
            $data['id'] = $user->getId();
            $data['firstname'] = $user->getFirstname();
            $data['lastname'] = $user->getLastname();
            $event->setData($data);
        }
    }
}
