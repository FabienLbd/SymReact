<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InvoiceRepository")
 * @ApiResource(
 *     subresourceOperations={
 *          "api_customers_invoices_get_subresource"={
 *              "normalization_context"={"groups"={"invoices_subresource"}}
 *     }
 *     },
 *     attributes={
 *          "pagination_enabled"=false,
 *          "pagination_items_per_page"=20,
 *          "order": {"chrono":"desc"}
 *     },
 *     normalizationContext={
 *          "groups"={"invoices_read"}
 *     },
 *     denormalizationContext={"disable_type_enforcement"=true}
 * )
 * @ApiFilter(OrderFilter::class, properties={"amount", "sentAt", "status", "chrono"})
 */
class Invoice
{
    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"invoices_read", "customers_read", "invoices_subresource"})
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     * @Groups({"invoices_read", "customers_read", "invoices_subresource"})
     * @Assert\NotBlank(message="Le montant de la facture est obligatoire")
     * @Assert\Type(type="numeric", message="Le montant de la facture doit être numerique !")
     */
    private $amount;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"invoices_read", "customers_read", "invoices_subresource"})
     * @Assert\DateTime(message="La date doit être au format YYYY-MM-DD")
     * @Assert\NotBlank(message="La date d'envoi doit être renseignée")
     */
    private $sentAt;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"invoices_read", "customers_read", "invoices_subresource"})
     * @Assert\NotBlank(message="Le statut de la facture est obligatoire")
     * @Assert\Choice(choices={"SENT", "PAID", "CANCELLED"}, message="Le statut doit être SENT, PAID, ou CANCELLED")
     *
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer", inversedBy="invoices")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"invoices_read", "invoices_subresource"})
     * @Assert\NotBlank(message="Le client de la facture est obligatoire")
     *
     */
    private $customer;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"invoices_read", "customers_read", "invoices_subresource"})
     * @Assert\NotBlank(message="Il faut absolument un chrono pour la facture")
     * @Assert\Type(type="integer", message="Le chrono doit être un nombre entier")
     */
    private $chrono;

    /**
     * @ORM\Column(type="float")
     * @Groups({"invoices_read", "customers_read", "invoices_subresource"})
     * @Assert\NotBlank(message="Le montant des frais est obligatoire")
     * @Assert\Type(type="numeric", message="Le montant des frais doit être numerique !")
     */
    private $fee;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"invoices_read", "invoices_subresource"})
     * @Assert\Type(type="numeric", message="Le montant doit être numerique !")
     */
    private $feeReminder = 0;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"invoices_read", "invoices_subresource"})
     * @Assert\Type(type="numeric", message="Le montant doit être numerique !")
     */
    private $amountReminder = 0;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"invoices_read", "invoices_subresource"})
     */
    private $isReminderInvoice = false;

    /**
     * @Groups({"invoices_read", "invoices_subresource"})
     * @return User
     */
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

    public function setAmount($amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt($sentAt): self
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

    public function setChrono($chrono): self
    {
        $this->chrono = $chrono;

        return $this;
    }

    public function getFee(): ?float
    {
        return $this->fee;
    }

    public function setFee($fee): self
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

    /**
     * @Groups({"invoices_read", "invoices_subresource"})
     * @SerializedName("total")
     * @return float
     */
    public function getTotalAmountTTC(): float
    {
        $sum = $this->getAmount() + $this->getFee() + $this->getFeeReminder() + $this->getAmountReminder();
        return round($sum + ($sum * 0.20), 2);
    }

    /**
     * @Groups({"invoices_read"})
     * @SerializedName("totalHT")
     * @return float
     */
    public function getTotalAmountHT(): float
    {
        $sum = $this->getAmount() + $this->getFee() + $this->getFeeReminder() + $this->getAmountReminder();
        return round($sum, 2);
    }

    /**
     * @Groups({"invoices_read"})
     * @SerializedName("tvaAmount")
     * @return float
     */
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
