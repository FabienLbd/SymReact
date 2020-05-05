<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use App\Controller\UserResetPasswordAction;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ApiResource(
 *     collectionOperations={
 *         "get"={
 *              "access_control"="is_granted('ROLE_ADMIN')"
 *          },
 *         "post"={
 *              "validation_groups"={"Default", "user_write"},
 *              "denormalization_context"={"groups"={"user_write"}}
 *         },
 *          "reset_password_request"={
 *              "method"="POST",
 *              "path"="/users/resetPasswordRequest",
 *              "denormalization_context"={"groups"={"reset_password_request"}},
 *              "validation_groups"={"reset_password_request"}
 *          }
 *     },
 *     itemOperations={
 *          "get"={
 *              "access_control"="(is_granted('ROLE_ADMIN') or is_granted('ROLE_USER') and object == user)",
 *              "normalization_context"={"groups"={"users_read"}}
 *          },
 *          "put"={
 *              "access_control"="(is_granted('ROLE_ADMIN') or is_granted('ROLE_USER') and object == user)",
 *              "denormalization_context"={"groups"={"users_put"}}
 *          },
 *          "delete"={
 *             "access_control"="is_granted('ROLE_ADMIN')"
 *          },
 *          "update_password"={
 *              "method"="PUT",
 *              "path"="/users/{id}/updatePassword",
 *              "access_control"="(is_granted('ROLE_ADMIN') or is_granted('ROLE_USER') and object == user)",
 *              "denormalization_context"={"groups"={"update_password"}},
 *              "validation_groups"={"update_password"}
 *          },
 *          "reset_password"={
 *              "method"="PUT",
 *              "path"="/users/{token}/resetPassword",
 *              "denormalization_context"={"groups"={"reset_password"}},
 *              "validation_groups"={"reset_password"},
 *              "controller"=UserResetPasswordAction::class,
 *              "defaults"={"identifiedBy"="token"},
 *               "read"=false
 *          }
 *     },
 * )
 * @UniqueEntity("email", message="Un utilisateur ayant cette adresse email existe déjà")
 */
class User implements UserInterface
{
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_USER = 'ROLE_USER';
    const DEFAULT_ROLE = 'ROLE_USER';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"customers_read", "invoices_read", "invoices_subresource", "users_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups(
     *     {
     *     "customers_read",
     *     "invoices_read",
     *     "invoices_subresource",
     *     "users_read",
     *     "user_write",
     *     "reset_password_request"
     *     }
     *)
     * @Assert\NotBlank(
     *     message="L'email doit être renseigné",
     *     groups={"reset_password_request"}
     * )
     * @Assert\Email(
     *     message="L'adresse email doit avoir un format valide",
     *     groups={"reset_password_request"}
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @Groups({"update_password", "user_write", "reset_password"})
     * @SerializedName("password")
     * @Assert\NotBlank(
     *     message="Le mot de passe est obligatoire",
     *     groups={"user_write", "update_password", "reset_password"}
     *     )
     */
    private $plainPassword;

    /**
     * @Groups({"update_password"})
     * @Assert\NotBlank(
     *     message="Le mot de passe est obligatoire",
     *     groups={"update_password"}
     *     )
     * @SecurityAssert\UserPassword(
     *     groups={"update_password"},
     *     message = "Le mot de passe donné ne correspond pas !"
     * )
     */
    private $oldPassword;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @var string The hashed confirmed password
     * @Groups({"update_password", "user_write", "reset_password"})
     * @Assert\IdenticalTo(
     *     propertyPath="plainPassword",
     *     message="La confirmation du mot de passe n'est pas valide",
     *     groups={"user_write", "update_password", "reset_password"}
     *     )
     */
    private $passwordConfirm;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"customers_read", "invoices_read", "invoices_subresource", "users_read", "users_put", "user_write"})
     * @Assert\NotBlank(
     *     message="Le prénom est obligatoire",
     * )
     * @Assert\Length(
     *      min=3,
     *      minMessage="Le prénom doit faire entre 3 et 255 caractères",
     *      max=255, maxMessage="Le prénom doit faire entre 3 et 255 caractères"
     * )
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"customers_read", "invoices_read", "invoices_subresource", "users_read", "users_put", "user_write"})
     * @Assert\NotBlank(message="Le nom de famille est obligatoire")
     * @Assert\Length(
     *      min=3,
     *      minMessage="Le nom de famille doit faire entre 3 et 255 caractères",
     *      max=255, maxMessage="Le nom de famille doit faire entre 3 et 255 caractères"
     * )
     */
    private $lastname;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer", mappedBy="user")
     */
    private $customers;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"customers_read", "invoices_read", "invoices_subresource", "users_read", "users_put", "user_write"})
     * @Assert\NotBlank(message="L'adresse est obligatoire")
     */
    private $address;

    /**
     * @ORM\Column(type="string")
     * @Groups({"customers_read", "invoices_read", "invoices_subresource", "users_read", "users_put", "user_write"})
     * @Assert\NotBlank(message="Le code postal est obligatoire")
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string")
     * @Groups({"customers_read", "invoices_read", "invoices_subresource", "users_read", "users_put", "user_write"})
     * @Assert\NotBlank(message="Le numéro de TVA est obligatoire")
     */
    private $numTVA;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"customers_read", "invoices_read", "invoices_subresource", "users_read", "users_put", "user_write"})
     * @Assert\NotBlank(message="Le nom de la société est obligatoire")
     */
    private $company;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"customers_read", "invoices_read", "invoices_subresource", "users_read", "users_put", "user_write"})
     * @Assert\NotBlank(message="Le nom de la ville est obligatoire")
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"customers_read", "invoices_read", "invoices_subresource", "users_read", "users_put", "user_write"})
     * @Assert\NotBlank(message="Le numéro de téléphone est obligatoire")
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     * @Groups({"reset_password"})
     */
    private $resetPasswordToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $resetPasswordGeneratedAt;

    public function __construct()
    {
        $this->customers = new ArrayCollection();
        $this->roles[] = self::DEFAULT_ROLE;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return string
     */
    public function getPasswordConfirm(): ?string
    {
        return $this->passwordConfirm;
    }

    /**
     * @param string $passwordConfirm
     */
    public function setPasswordConfirm(string $passwordConfirm): void
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

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param mixed $plainPassword
     */
    public function setPlainPassword($plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }



    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
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

    /**
     * @return Collection|Customer[]
     */
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

    public function getNumTVA(): ?string
    {
        return $this->numTVA;
    }

    public function setNumTVA(string $numTVA): self
    {
        $this->numTVA = $numTVA;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(string $company): self
    {
        $this->company = $company;

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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOldPassword()
    {
        return $this->oldPassword;
    }

    /**
     * @param mixed $oldPassword
     */
    public function setOldPassword($oldPassword): void
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
