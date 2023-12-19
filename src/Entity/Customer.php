<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(),
        new Put(),
        new Delete(),
        new GetCollection(),
        new Post(),
    ],
    normalizationContext: ['groups' => ['customers_read']],
    order: ['lastname' => 'ASC']
)]
#[ApiFilter(filterClass: BooleanFilter::class, properties: ['isArchived'])]
#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields.
     */
    use TimestampableEntity;

    #[Groups(['customers_read', 'invoices_read', 'invoices_subresource'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[Groups(['customers_read', 'invoices_read'])]
    #[Assert\NotBlank(message: 'Le prénom du customer est obligatoire')]
    #[Assert\Length(min: 3, minMessage: 'Le prénom doit faire entre 3 et 255 caractères', max: 255, maxMessage: 'Le prénom doit faire entre 3 et 255 caractères')]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $firstname = null;

    #[Groups(['customers_read', 'invoices_read', 'invoices_subresource'])]
    #[Assert\NotBlank(message: 'Le nom de famille du customer est obligatoire')]
    #[Assert\Length(min: 3, minMessage: 'Le nom de famille doit faire entre 3 et 255 caractères', max: 255, maxMessage: 'Le prénom doit faire entre 3 et 255 caractères')]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $lastname = null;

    #[Groups(['customers_read', 'invoices_read'])]
    #[Assert\NotBlank(message: "L'adresse email du customer est obligatoire")]
    #[Assert\Email(message: "Le format de l'adresse email doit être valide")]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $email = null;

    #[Groups(['customers_read', 'invoices_read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $company = null;

    #[Groups(['customers_read'])]
    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Invoice::class)]
    private Collection $invoices;

    #[Groups(['customers_read', 'invoices_read'])]
    #[Assert\NotBlank(message: "L'utilisateur est obligatoire")]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'customers')]
    private ?User $user = null;

    #[Groups(['customers_read', 'invoices_read'])]
    #[Assert\NotBlank(message: "L'adresse est obligatoire")]
    #[Assert\Length(min: 3, minMessage: "L'adresse doit faire entre 3 et 255 caractères", max: 255, maxMessage: "L'adresse' doit faire entre 3 et 255 caractères")]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $address = null;

    #[Groups(['customers_read', 'invoices_read'])]
    #[Assert\NotBlank(message: 'Le code postal est obligatoire')]
    #[ORM\Column(type: 'string')]
    private ?string $postalCode = null;

    #[Groups(['customers_read', 'invoices_read'])]
    #[Assert\NotBlank(message: 'La ville est obligatoire')]
    #[Assert\Length(min: 3, minMessage: 'Le nom de la ville doit faire entre 3 et 255 caractères', max: 255, maxMessage: 'Le nom de la ville doit faire entre 3 et 255 caractères')]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $city = null;

    #[Groups(['customers_read'])]
    #[ORM\Column(type: 'boolean')]
    private bool $isArchived = false;

    public function __construct()
    {
        $this->invoices = new ArrayCollection();
    }

    #[Groups(['customers_read'])]
    #[SerializedName('totalAmount')]
    public function getTotalAmount(): float
    {
        return array_reduce($this->invoices->toarray(), static fn ($total, $invoice): int|float => $total + ('PAID' === $invoice->getStatus() ? $invoice->getTotalAmountTTC() : 0), 0);
    }

    #[Groups(['customers_read'])]
    public function getUnpaidAmount(): float
    {
        return array_reduce($this->invoices->toArray(), static fn ($total, $invoice): int|float => $total + ('PAID' === $invoice->getStatus() || 'CANCELLED' === $invoice->getstatus() ? 0 : $invoice->getAmount()), 0);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return ucfirst((string) $this->firstname);
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return ucfirst((string) $this->lastname);
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCompany(): ?string
    {
        return ucfirst((string) $this->company);
    }

    public function setCompany(?string $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices[] = $invoice;
            $invoice->setCustomer($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->contains($invoice)) {
            $this->invoices->removeElement($invoice);
            // set the owning side to null (unless already changed)
            if ($invoice->getCustomer() === $this) {
                $invoice->setCustomer(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return ucfirst((string) $this->city);
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getIsArchived(): ?bool
    {
        return $this->isArchived;
    }

    public function setIsArchived(bool $isArchived): self
    {
        $this->isArchived = $isArchived;

        return $this;
    }
}
