<?php

namespace App\Tests\functional;

use App\Entity\User;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Zenstruck\Browser\HttpOptions;
use Zenstruck\Browser\KernelBrowser;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Proxy;

abstract class ApiTestCase extends KernelTestCase
{
    use HasBrowser {
        browser as baseKernelBrowser;
    }

    protected function browser(array $options = [], array $server = []): KernelBrowser
    {
        return $this->baseKernelBrowser($options, $server)
            ->setDefaultHttpOptions(
                HttpOptions::create()
                    ->withHeader('Accept', 'application/ld+json')
            )
        ;
    }

    protected function getAuthenticationTokenForAdmin(): string
    {
        $user = $this->getAdminUser();

        return $this
            ->browser()
            ->post('/api/login_check', [
                'json' => [
                    'email' => $user->getUserIdentifier(),
                    'password' => 'admin',
                ],
            ])
            ->json()
            ->decoded()['token']
        ;
    }

    protected function getAuthenticationTokenForUser(): string
    {
        $user = $this->getUser();

        return $this
            ->browser()
            ->post('/api/login_check', [
                'json' => [
                    'email' => $user->getUserIdentifier(),
                    'password' => 'user',
                ],
            ])
            ->json()
            ->decoded()['token']
        ;
    }

    protected function getAdminUser(): UserInterface
    {
        return UserFactory::createOne([
            'email' => 'admin@test.com',
            'roles' => [User::ROLE_ADMIN],
            'password' => 'admin',
        ]);
    }

    protected function getUser(): User|Proxy
    {
        return UserFactory::createOne([
            'email' => 'user@test.com',
            'password' => 'user',
        ]);
    }
}
