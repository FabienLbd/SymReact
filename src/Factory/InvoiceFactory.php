<?php

namespace App\Factory;

use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Invoice>
 *
 * @method        Invoice|Proxy                     create(array|callable $attributes = [])
 * @method static Invoice|Proxy                     createOne(array $attributes = [])
 * @method static Invoice|Proxy                     find(object|array|mixed $criteria)
 * @method static Invoice|Proxy                     findOrCreate(array $attributes)
 * @method static Invoice|Proxy                     first(string $sortedField = 'id')
 * @method static Invoice|Proxy                     last(string $sortedField = 'id')
 * @method static Invoice|Proxy                     random(array $attributes = [])
 * @method static Invoice|Proxy                     randomOrCreate(array $attributes = [])
 * @method static InvoiceRepository|RepositoryProxy repository()
 * @method static Invoice[]|Proxy[]                 all()
 * @method static Invoice[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Invoice[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Invoice[]|Proxy[]                 findBy(array $attributes)
 * @method static Invoice[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Invoice[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class InvoiceFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function getDefaults(): array
    {
        return [
            'amount' => self::faker()->randomFloat(),
            'chrono' => self::faker()->randomNumber(),
            'fee' => self::faker()->randomFloat(),
            'sentAt' => self::faker()->dateTime(),
            'status' => self::faker()->randomElement(['SENT', 'PAID', 'CANCELLED']),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Invoice $invoice): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Invoice::class;
    }
}
