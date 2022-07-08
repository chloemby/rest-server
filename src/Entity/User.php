<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\UserRole;
use Doctrine\ORM\Mapping as ORM;
use Rollerworks\Component\PasswordStrength\Validator\Constraints\PasswordStrength;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(groups: ['non_sensitive'])]
    private int $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\Length(
        min: 4,
        max: 30,
        minMessage: 'Логин должен быть не короче {{ limit }} символов',
        maxMessage: 'Логин не должен быть длиннее {{ limit }} символов'
    )]
    #[Groups(groups: ['non_sensitive'])]
    private string $username;

    #[ORM\Column(type: 'json')]
    #[Groups(groups: ['sensitive'])]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    #[PasswordStrength(minStrength: 4)]
    #[Groups(groups: ['sensitive'])]
    private string $password;

    #[ORM\Column(type: 'string', unique: true)]
    #[Assert\NotBlank(message: 'Не указан адрес электронной почты')]
    #[Assert\Email(message: 'Некорректный адрес электронной почты')]
    #[Groups(groups: ['sensitive'])]
    private string $email;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false)]
    #[Groups(groups: ['non_sensitive'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(
        name: 'deleted_at',
        type: 'datetime_immutable',
        nullable: true,
        options: ['default' => null]
    )]
    #[Groups(groups: ['non_sensitive'])]
    private ?\DateTimeImmutable $deletedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * @see UserInterface
     * @return string[] {@see UserRole}
     */
    public function getRoles(): array
    {
        return array_unique($this->roles);
    }

    public function hasRole(UserRole $userRole): bool
    {
        return \in_array($userRole->value, $this->getRoles());
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt()
    {}

    public function eraseCredentials()
    {}

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'deletedAt' => $this->getDeletedAt()?->getTimestamp(),
            'createdAt' => $this->getCreatedAt()->getTimestamp(),
            'username' => $this->getUserIdentifier(),
        ];
    }
}
