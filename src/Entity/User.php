<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\UserResetPasswordAction;
use App\Repository\UserRepository;
use App\State\UserProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['users_read']],
            security: '(is_granted(\'ROLE_ADMIN\') or is_granted(\'ROLE_USER\') and object == user)'
        ),
        new Put(
            denormalizationContext: ['groups' => ['users_put']],
            security: '(is_granted(\'ROLE_ADMIN\') or is_granted(\'ROLE_USER\') and object == user)'
        ),
        new Delete(security: 'is_granted(\'ROLE_ADMIN\')'),
        new Put(
            uriTemplate: '/users/{id}/updatePassword',
            denormalizationContext: ['groups' => ['update_password']],
            security: '(is_granted(\'ROLE_ADMIN\') or is_granted(\'ROLE_USER\') and object == user)', validationContext: ['groups' => ['update_password']]
        ),
        new Put(
            uriTemplate: '/users/{token}/resetPassword',
            defaults: ['identifiedBy' => 'token'],
            controller: UserResetPasswordAction::class,
            denormalizationContext: ['groups' => ['reset_password']],
            validationContext: ['groups' => ['reset_password']], read: false
        ),
        new GetCollection(security: 'is_granted(\'ROLE_ADMIN\')'),
        new Post(
            denormalizationContext: ['groups' => ['user_write']],
            validationContext: ['groups' => ['Default', 'user_write']]
        ),
        new Post(
            uriTemplate: '/users/resetPasswordRequest',
            denormalizationContext: ['groups' => ['reset_password_request']],
            validationContext: ['groups' => ['reset_password_request']]),
    ],
    processor: UserProcessor::class
)
]
#[UniqueEntity('email', message: 'Un utilisateur ayant cette adresse email existe déjà')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    final public const ROLE_ADMIN = 'ROLE_ADMIN';
    final public const ROLE_USER = 'ROLE_USER';
    final public const DEFAULT_ROLE = self::ROLE_USER;

    #[Groups(['customers_read', 'invoices_read', 'invoices_subresource', 'users_read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[Groups(['customers_read', 'invoices_read', 'invoices_subresource', 'users_read', 'user_write', 'reset_password_request'])]
    #[Assert\NotBlank(message: "L'email doit être renseigné", groups: ['reset_password_request'])]
    #[Assert\Email(message: "L'adresse email doit avoir un format valide", groups: ['reset_password_request'])]
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[Groups(['update_password', 'user_write', 'reset_password'])]
    #[SerializedName('password')]
    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire', groups: ['user_write', 'update_password', 'reset_password'])]
    private ?string $plainPassword = null;

    #[SecurityAssert\UserPassword(message: 'Le mot de passe donné ne correspond pas !', groups: ['update_password'])]
    #[Groups(['update_password'])]
    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire', groups: ['update_password'])]
    private ?string $oldPassword = null;

    #[ORM\Column(type: 'string')]
    private ?string $password = null;

    #[Groups(['update_password', 'user_write', 'reset_password'])]
    #[Assert\IdenticalTo(propertyPath: 'plainPassword', message: "La confirmation du mot de passe n'est pas valide", groups: ['user_write', 'update_password', 'reset_password'])]
    private ?string $passwordConfirm = null;

    #[Groups(['customers_read', 'invoices_read', 'invoices_subresource', 'users_read', 'users_put', 'user_write'])]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire')]
    #[Assert\Length(min: 3, minMessage: 'Le prénom doit faire entre 3 et 255 caractères', max: 255, maxMessage: 'Le prénom doit faire entre 3 et 255 caractères')]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $firstname = null;

    #[Groups(['customers_read', 'invoices_read', 'invoices_subresource', 'users_read', 'users_put', 'user_write'])]
    #[Assert\NotBlank(message: 'Le nom de famille est obligatoire')]
    #[Assert\Length(min: 3, minMessage: 'Le nom de famille doit faire entre 3 et 255 caractères', max: 255, maxMessage: 'Le nom de famille doit faire entre 3 et 255 caractères')]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $lastname = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Customer::class)]
    private Collection $customers;

    #[Groups(['customers_read', 'invoices_read', 'invoices_subresource', 'users_read', 'users_put', 'user_write'])]
    #[Assert\NotBlank(message: "L'adresse est obligatoire")]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $address = null;

    #[Groups(['customers_read', 'invoices_read', 'invoices_subresource', 'users_read', 'users_put', 'user_write'])]
    #[Assert\NotBlank(message: 'Le code postal est obligatoire')]
    #[ORM\Column(type: 'string')]
    private ?string $postalCode = null;

    #[Groups(['customers_read', 'invoices_read', 'invoices_subresource', 'users_read', 'users_put', 'user_write'])]
    #[Assert\NotBlank(message: 'Le numéro de TVA est obligatoire')]
    #[ORM\Column(type: 'string')]
    private ?string $numTVA = null;

    #[Groups(['customers_read', 'invoices_read', 'invoices_subresource', 'users_read', 'users_put', 'user_write'])]
    #[Assert\NotBlank(message: 'Le nom de la société est obligatoire')]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $company = null;

    #[Groups(['customers_read', 'invoices_read', 'invoices_subresource', 'users_read', 'users_put', 'user_write'])]
    #[Assert\NotBlank(message: 'Le nom de la ville est obligatoire')]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $city = null;

    #[Groups(['customers_read', 'invoices_read', 'invoices_subresource', 'users_read', 'users_put', 'user_write'])]
    #[Assert\NotBlank(message: 'Le numéro de téléphone est obligatoire')]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $phone = null;

    #[Groups(['reset_password'])]
    #[ORM\Column(type: 'string', length: 40, nullable: true)]
    private ?string $resetPasswordToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $resetPasswordGeneratedAt = null;

    public function __construct()
    {
        $this->customers = new ArrayCollection();
        $this->roles[] = self::DEFAULT_ROLE;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * The public representation of the user (e.g. a username, an email address, etc.).
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPasswordConfirm(): ?string
    {
        return $this->passwordConfirm;
    }

    public function setPasswordConfirm(?string $passwordConfirm): void
    {
        $this->passwordConfirm = $passwordConfirm;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
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

    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    public function addCustomer(Customer $customer): self
    {
        if (!$this->customers->contains($customer)) {
            $this->customers[] = $customer;
            $customer->setUser($this);
        }

        return $this;
    }

    public function removeCustomer(Customer $customer): self
    {
        if ($this->customers->contains($customer)) {
            $this->customers->removeElement($customer);
            // set the owning side to null (unless already changed)
            if ($customer->getUser() === $this) {
                $customer->setUser(null);
            }
        }

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

    public function getNumTVA(): ?string
    {
        return $this->numTVA;
    }

    public function setNumTVA(?string $numTVA): self
    {
        $this->numTVA = $numTVA;

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

    public function getCity(): ?string
    {
        return ucfirst((string) $this->city);
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setOldPassword(?string $oldPassword): void
    {
        $this->oldPassword = $oldPassword;
    }

    public function getResetPasswordToken(): ?string
    {
        return $this->resetPasswordToken;
    }

    public function setResetPasswordToken(?string $resetPasswordToken): self
    {
        $this->resetPasswordToken = $resetPasswordToken;

        return $this;
    }

    public function getResetPasswordGeneratedAt(): ?\DateTimeInterface
    {
        return $this->resetPasswordGeneratedAt;
    }

    public function setResetPasswordGeneratedAt(?\DateTimeInterface $resetPasswordGeneratedAt): self
    {
        $this->resetPasswordGeneratedAt = $resetPasswordGeneratedAt;

        return $this;
    }
}
