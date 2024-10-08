<?php

namespace App\Interfaces;

use App\Models\User;

interface AuthServiceInterface {
    public function register(array $data): User | bool;
    public function login(array $data): array;
    public function logout(User $user): bool;
    public function verifyEmail(int $userId): bool;
    public function resendEmail(User $user): bool;
    public function requestPasswordReset(int $userId): bool;
    public function resetPassword(array $data): bool;
}