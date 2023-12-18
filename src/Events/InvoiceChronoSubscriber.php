<?php

namespace App\Events;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

class InvoiceChronoSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly Security $security, private readonly InvoiceRepository $invoiceRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['setChronoForInvoice', EventPriorities::PRE_VALIDATE],
        ];
    }

    public function setChronoForInvoice(ViewEvent $event): void
    {
        $result = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if ($result instanceof Invoice && 'POST' === $method) {
            $nextChrono = $this->invoiceRepository->findNextChrono($this->security->getUser());
            $result->setChrono($nextChrono);
            if (!$result->getSentAt() instanceof \DateTimeInterface) {
                $result->setSentAt(new \DateTime());
            }
        }
    }
}
