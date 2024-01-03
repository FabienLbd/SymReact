<?php

namespace App\Tests\functional;

use App\Factory\CustomerFactory;
use App\Factory\InvoiceFactory;
use Zenstruck\Foundry\Test\ResetDatabase;

class InvoiceResourceTest extends ApiTestCase
{
    use ResetDatabase;

    public function testGetCollectionOfInvoices(): void
    {
        $user = $this->getUser();

        $customer = CustomerFactory::createOne([
            'user' => $user,
        ]);
        InvoiceFactory::createMany(10, static function () use ($customer) {
            return [
                'customer' => $customer,
            ];
        });

        $json = $this
            ->browser()
            ->actingAs($user)
            ->get('/api/invoices')
            ->assertJson()
            ->assertJsonMatches('"hydra:totalItems"', 10)
            ->assertJsonMatches('length("hydra:member")', 10)
            ->json()
        ;
    }
}
