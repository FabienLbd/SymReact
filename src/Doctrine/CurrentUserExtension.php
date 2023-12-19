<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

final class CurrentUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private const USER_PARAMETER = 'user';

    public function __construct(
        private readonly Security $security,
        private readonly AuthorizationCheckerInterface $auth
    ) {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if (!$user) {
            return;
        }

        $allowedClasses = [Customer::class, Invoice::class];

        if (in_array($resourceClass, $allowedClasses) && !$this->auth->isGranted(User::ROLE_ADMIN)) {
            switch ($resourceClass) {
                case Customer::class:
                    $this->addCustomerWhere($queryBuilder);
                    break;
                case Invoice::class:
                    $this->addInvoiceWhere($queryBuilder);
                    break;
            }
        }
    }

    private function addCustomerWhere(QueryBuilder $queryBuilder): void
    {
        $rootalias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->andWhere("$rootalias.user = :".self::USER_PARAMETER);
        $queryBuilder->setParameter(self::USER_PARAMETER, $this->security->getUser());
    }

    private function addInvoiceWhere(QueryBuilder $queryBuilder): void
    {
        $rootalias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->join("$rootalias.customer", 'c')->andWhere('c.user = :'.self::USER_PARAMETER);
        $queryBuilder->setParameter(self::USER_PARAMETER, $this->security->getUser());
    }
}
