<?php

namespace App\Infrastructure\Security\Providers;

use App\Domain\Repository\AdminUserRepositoryInterface;
use App\Infrastructure\Security\AuthUser;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AdminUserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly AdminUserRepositoryInterface $adminUserRepository,
    ) {}

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $adminUser = $this->adminUserRepository->findByEmail($identifier);

        if (!$adminUser) {
            throw new UserNotFoundException("User not Found {$identifier}");
        }

        return new AuthUser(
            $adminUser->getId(),
            $adminUser->getName(),
            $adminUser->getEmail(),
            $adminUser->getPassword(),
            $adminUser->getRole()
        );
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return AuthUser::class === $class || is_subclass_of($class, AuthUser::class);
    }
}
