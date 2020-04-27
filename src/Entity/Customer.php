<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CustomerRepository")
 * @ApiResource(
 *     collectionOperations={"GET", "POST"},
 *     itemOperations={"GET", "PUT", "DELETE"},
 *     subresourceOperations={
 *          "invoices_get_subresource"={"path"="/customers/{id}/invoices"}
 *     },
 *     normalizationContext={
 *          "groups"={"customers_read"}
 *     }
 * )
 * @ApiFilter(BooleanFilter::class, properties={"isArchived"})
 *
 */
class Customer
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
     * @Groups({"customers_read", "invoices_read", "invoices_subresource"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"customers_read", "invoices_read"})
     * @Assert\NotBlank(message="Le prénom du customer est obligatoire")
     * @Assert\Length(
     *      min=3,
     *      minMessage="Le prénom doit faire entre 3 et 255 caractères",
     *      max=255, maxMessage="Le prénom doit faire entre 3 et 255 caractères"
     * )
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"customers_read", "invoices_read", "invoices_subresource"})
     * @Assert\NotBlank(message="Le nom de famille du customer est obligatoire")
     * @Assert\Length(
     *      min=3,
     *      minMessage="Le nom de famille doit faire entre 3 et 255 caractères",
     *      max=255, maxMessage="Le prénom doit faire entre 3 et 255 caractères"
     * )
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"customers_read", "invoices_read"})
     * @Assert\NotBlank(message="L'adresse email du customer est obligatoire")
     * @Assert\Email(message="Le format de l'adresse email doit être valide")
     *
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"customers_read", "invoices_read"})
     */
    private $company;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Invoice", mappedBy="customer")
     * @Groups({"customers_read"})
     * @ApiSubresource
     */
    private $invoices;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="customers")
     * @Groups({"customers_read", "invoices_read"})
     * @Assert\NotBlank(message="L'utilisateur est obligatoire")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"customers_read", "invoices_read"})
     * @Assert\NotBlank(message="L'adresse est obligatoire")
     * @Assert\Length(
     *      min=3,
     *      minMessage="L'adresse doit faire entre 3 et 255 caractères",
     *      max=255,
     *      maxMessage="L'adresse' doit faire entre 3 et 255 caractères"
     * )
     */
    private $address;

    /**
     * @ORM\Column(type="string")
     * @Groups({"customers_read", "invoices_read"})
     * @Assert\NotBlank(message="Le code postal est obligatoire")
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"customers_read", "invoices_read"})
     * @Assert\NotBlank(message="La ville est obligatoire")
     * @Assert\Length(
     *      min=3,
     *      minMessage="Le nom de la ville doit faire entre 3 et 255 caractères",
     *      max=255,
     *      maxMessage="Le nom de la ville doit faire entre 3 et 255 caractères"
     * )
     */
    private $city;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"customers_read"})
     */
    private $isArchived = false;

    public function __construct()
    {
        $this->invoices = new ArrayCollection();
    }

    /**
     * @Groups({"customers_read"})
     * @return float
     */
    public function getTotalAmount(): float
    {
        return array_reduce($this->invoices->toarray(), function($total, $invoice) {
            return $total + $invoice->getAmount() + $invoice->getFee();
        }, 0);
    }

    /**
     * @Groups({"customers_read"})
     * @return float
     */
    public function getUnpaidAmount(): float
    {
        return array_reduce($this->invoices->toArray(), function($total, $invoice) {
            return $total + ($invoice->getStatus() === "PAID" || $invoice->getstatus() === "CANCELLED" ? 0 : $invoice->getAmount());
        }, 0);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection|Invoice[]
     */
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

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
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
