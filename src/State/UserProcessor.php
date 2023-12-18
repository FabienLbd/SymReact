<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $decorated,
        private readonly UserPasswordHasherInterface $passwordEncoder
    ) {
    }

    /**
     * @param array<array-key, mixed> $uriVariables
     * @param array<array-key, mixed> $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof User && $data->getPlainPassword()) {
            $hash = $this->passwordEncoder->hashPassword($data, $data->getPlainPassword());
            $data->setPassword($hash);
            $data->eraseCredentials();
        }

        $this->decorated->process($data, $operation, $uriVariables, $context);

        return $data;
    }
}
