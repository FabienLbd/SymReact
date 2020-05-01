<?php


namespace App\DataPersister;


use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class UserDataPersister implements ContextAwareDataPersisterInterface
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof User;
    }

    /**
     * @param $data User
     * @param array $context
     * @return object|void
     */
    public function persist($data, array $context = [])
    {
        if ($data->getPlainPassword()) {
            if ($context['item_operation_name'] === 'reset_password') {
                $newEncodedPassword =
                    $this->passwordEncoder->encodePassword($data, $data->getPlainPassword());
                $data->setPassword($newEncodedPassword);
            }
            $data->eraseCredentials();
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }

    public function remove($data, array $context = [])
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }
}