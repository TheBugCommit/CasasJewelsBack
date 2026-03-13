<?php

namespace App\Infrastructure\Security;
use App\Domain\ValueObject\UserRole;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $email,
        private readonly string $password,
        private readonly UserRole $role
    ) {}

    public function getUserIdentifier(): string { return $this->email; }
    public function getRoles(): array {
        $symfonyRole = match($this->role) {
            UserRole::ADMIN    => 'ROLE_ADMIN',
            UserRole::CUSTOMER => 'ROLE_USER',
        };

        $role = [$symfonyRole];
        $role[] = "ROLE_USER";

        return array_unique($role);
    }
    public function getPassword(): string { return $this->password; }
    public function getId(): string { return $this->id; }

    public function getName(): string { return $this->name; }

    public function getRole(): UserRole
    {
        return $this->role;
    }
    public function eraseCredentials(): void {}
}
