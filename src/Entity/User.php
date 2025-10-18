<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "users")]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[OA\Schema(
    schema: 'User',
    title: 'User',
    description: 'Modèle d\'utilisateur',
    required: ['email', 'password'],
    type: 'object',
    properties: [
        new OA\Property(
            property: 'id',
            type: 'integer',
            description: 'Identifiant unique de l\'utilisateur',
            example: 1
        ),
        new OA\Property(
            property: 'email',
            type: 'string',
            description: 'Adresse email de l\'utilisateur (utilisée pour l\'authentification)',
            format: 'email',
            maxLength: 180,
            example: 'user@example.com'
        ),
        new OA\Property(
            property: 'roles',
            type: 'array',
            description: 'Rôles de l\'utilisateur',
            items: new OA\Items(type: 'string', example: 'ROLE_USER'),
            example: ['ROLE_USER']
        ),
        new OA\Property(
            property: 'password',
            type: 'string',
            description: 'Mot de passe de l\'utilisateur (8-64 caractères)',
            format: 'password',
            minLength: 8,
            maxLength: 64,
            example: 'password123'
        )
    ]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups("user")]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups("user")]
    #[Assert\NotBlank(message: 'L’adresse e-mail est obligatoire.')]
    #[Assert\Email(
        message: 'L’adresse e-mail "{{ value }}" n’est pas valide.',
    )]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups("user")]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Groups("user")]
    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire.')]
    #[Assert\Length(
        min: 8,
        max: 64,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le mot de passe ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/[A-Z]/',
        message: 'Le mot de passe doit contenir au moins une lettre majuscule.'
    )]
    #[Assert\Regex(
        pattern: '/[a-z]/',
        message: 'Le mot de passe doit contenir au moins une lettre minuscule.'
    )]
    #[Assert\Regex(
        pattern: '/\d/',
        message: 'Le mot de passe doit contenir au moins un chiffre.'
    )]
    #[Assert\Regex(
        pattern: '/[\W_]/',
        message: 'Le mot de passe doit contenir au moins un caractère spécial.'
    )]
    private ?string $password = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }
}
