<?php

namespace App\Tests\functional;

use App\Factory\UserFactory;
use Zenstruck\Foundry\Test\ResetDatabase;

class LoginTest extends ApiTestCase
{
    use ResetDatabase;

    public function testLoginSuccess(): void
    {
        UserFactory::createOne([
            'email' => 'test@test.com',
            'password' => 'test',
        ]);

        $this
            ->browser()
            ->post('/api/login_check', [
                'json' => [
                    'email' => 'test@test.com',
                    'password' => 'test',
                ],
            ])
            ->assertSuccessful()
        ;
    }

    public function testLoginFail(): void
    {
        UserFactory::createOne([
            'email' => 'test@test.com',
            'password' => 'test',
        ]);

        $this
            ->browser()
            ->post('/api/login_check', [
                'json' => [
                    'email' => 'test@test.com',
                    'password' => 'pass',
                ],
            ])
            ->assertStatus(401)
        ;
    }
}
