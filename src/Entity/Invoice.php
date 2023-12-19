<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    normalizationContext: ['groups' => ['invoices_read']],
    denormalizationContext: ['disable_type_enforcement' => true],
    order: ['chrono' => 'desc'],
    paginationEnabled: false,
    paginationItemsPerPage: 20)
]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['amount', 'sentAt', 'status', 'chrono'])]
#[ApiResource(
    uriTemplate: '/customers/{id}/invoices',
    operations: [new GetCollection()],
    uriVariables: ['id' => new Link(toProperty: 'customer', fromClass: Customer::class, identifiers: ['id'])],
    status: 200, normalizationContext: ['groups' => ['invoices_read']],
    filters: ['annotated_app_entity_invoice_api_platform_core_bridge_doctrine_orm_filter_order_filter'])
]
#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{
    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields.
     */
    use TimestampableEntity;

    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource'])]
    #[Assert\NotBlank(message: 'Le montant de la facture est obligatoire')]
    #[Assert\Type(type: 'numeric', message: 'Le montant de la facture doit être numerique !')]
    #[ORM\Column(type: 'float')]
    private ?float $amount = null;

    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource'])]
    #[Assert\DateTime(message: 'La date doit être au format YYYY-MM-DD')]
    #[Assert\NotBlank(message: "La date d'envoi doit être renseignée")]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $sentAt = null;

    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource'])]
    #[Assert\NotBlank(message: 'Le statut de la facture est obligatoire')]
    #[Assert\Choice(choices: ['SENT', 'PAID', 'CANCELLED'], message: 'Le statut doit être SENT, PAID, ou CANCELLED')]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $status = null;

    #[Groups(['invoices_read', 'invoices_subresource'])]
    #[Assert\NotBlank(message: 'Le client de la facture est obligatoire')]
    #[ORM\ManyToOne(targetEntity: \App\Entity\Customer::class, inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource'])]
    #[Assert\NotBlank(message: 'Il faut absolument un chrono pour la facture')]
    #[Assert\Type(type: 'integer', message: 'Le chrono doit être un nombre entier')]
    #[ORM\Column(type: 'integer')]
    private ?int $chrono = null;

    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource'])]
    #[Assert\NotBlank(message: 'Le montant des frais est obligatoire')]
    #[Assert\Type(type: 'numeric', message: 'Le montant des frais doit être numerique !')]
    #[ORM\Column(type: 'float')]
    private ?float $fee = null;

    #[Groups(['invoices_read', 'invoices_subresource'])]
    #[Assert\Type(type: 'numeric', message: 'Le montant doit être numerique !')]
    #[ORM\Column(type: 'float', nullable: true)]
    private float $feeReminder = 0;

    #[Groups(['invoices_read', 'invoices_subresource'])]
    #[Assert\Type(type: 'numeric', message: 'Le montant doit être numerique !')]
    #[ORM\Column(type: 'float', nullable: true)]
    private float $amountReminder = 0;

    #[Groups(['invoices_read', 'invoices_subresource'])]
    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $isReminderInvoice = false;

    #[Groups(['invoices_read', 'invoices_subresource'])]
    public function getUser(): User
    {
        return $this->customer->getUser();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTimeInterface $sentAt): self
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getChrono(): ?int
    {
        return $this->chrono;
    }

    public function setChrono(?int $chrono): self
    {
        $this->chrono = $chrono;

        return $this;
    }

    public function getFee(): ?float
    {
        return $this->fee;
    }

    public function setFee(?float $fee): self
    {
        $this->fee = $fee;

        return $this;
    }

    public function getFeeReminder(): ?float
    {
        return $this->feeReminder;
    }

    public function setFeeReminder(?float $feeReminder): self
    {
        $this->feeReminder = $feeReminder;

        return $this;
    }

    public function getAmountReminder(): ?float
    {
        return $this->amountReminder;
    }

    public function setAmountReminder(?float $amountReminder): self
    {
        $this->amountReminder = $amountReminder;

        return $this;
    }

    #[Groups(['invoices_read', 'invoices_subresource'])]
    #[SerializedName('total')]
    public function getTotalAmountTTC(): float
    {
        $sum = $this->getAmount() + $this->getFee() + $this->getFeeReminder() + $this->getAmountReminder();

        return round($sum + ($sum * 0.20), 2);
    }

    #[Groups(['invoices_read'])]
    #[SerializedName('totalHT')]
    public function getTotalAmountHT(): float
    {
        $sum = $this->getAmount() + $this->getFee() + $this->getFeeReminder() + $this->getAmountReminder();

        return round($sum, 2);
    }

    #[Groups(['invoices_read'])]
    #[SerializedName('tvaAmount')]
    public function getTvaAmount(): float
    {
        $sum = $this->getAmount() + $this->getFee() + $this->getFeeReminder() + $this->getAmountReminder();

        return round($sum * 0.20, 2);
    }

    public function getIsReminderInvoice(): ?bool
    {
        return $this->isReminderInvoice;
    }

    public function setIsReminderInvoice(?bool $isReminderInvoice): self
    {
        $this->isReminderInvoice = $isReminderInvoice;

        return $this;
    }
}
