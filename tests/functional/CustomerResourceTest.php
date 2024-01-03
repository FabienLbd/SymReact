<?php

namespace App\Tests\functional;

use App\Factory\CustomerFactory;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Test\ResetDatabase;

class CustomerResourceTest extends ApiTestCase
{
    use ResetDatabase;

    public function testGetCollectionOfCustomers(): void
    {
        $user = UserFactory::createOne();
        CustomerFactory::createMany(5, static function () use ($user) {
            return [
                'user' => $user,
            ];
        });

        $json = $this
            ->browser()
            ->actingAs($user)
            ->get('/api/customers')
            ->assertJson()
            ->assertJsonMatches('"hydra:totalItems"', 5)
            ->assertJsonMatches('length("hydra:member")', 5)
            ->json()
        ;

        $this->assertSame(array_keys($json->decoded()['hydra:member'][0]), [
            '@id',
            '@type',
            'id',
            'firstname',
            'lastname',
            'email',
            'company',
            'invoices',
            'user',
            'address',
            'postalCode',
            'city',
            'isArchived',
            'totalAmount',
            'unpaidAmount',
        ]);
    }

    public function testPostToCreateCustomerWithApiKey(): void
    {
        $this->browser()
            ->post('/api/customers', [
                'json' => [],
                'headers' => [
                    'Authorization' => 'Bearer '.$this->getAuthenticationTokenForUser(),
                ],
            ])
            ->assertStatus(422)
        ;
    }
}
