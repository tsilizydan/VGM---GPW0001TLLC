<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * User model.
 * Extends the base Model to get the shared PDO connection + query helpers.
 */
class User extends Model
{
    protected static string $table = 'users';

    // -----------------------------------------------------------------------
    // Reads
    // -----------------------------------------------------------------------

    /**
     * Find a user by their email address.
     *
     * @return array<string, mixed>|null
     */
    public static function findByEmail(string $email): ?array
    {
        return self::queryOne(
            'SELECT * FROM users WHERE email = ? LIMIT 1',
            [$email]
        );
    }

    /**
     * Find a user by their primary key.
     *
     * @return array<string, mixed>|null
     */
    public static function findById(int $id): ?array
    {
        return self::queryOne(
            'SELECT * FROM users WHERE id = ? LIMIT 1',
            [$id]
        );
    }

    /**
     * Find a user by their email verification token.
     *
     * @return array<string, mixed>|null
     */
    public static function findByVerificationToken(string $token): ?array
    {
        return self::queryOne(
            'SELECT * FROM users WHERE email_verification_token = ? LIMIT 1',
            [$token]
        );
    }

    // -----------------------------------------------------------------------
    // Writes
    // -----------------------------------------------------------------------

    /**
     * Create a new user and return the new ID.
     *
     * @param array<string, mixed> $data  Keys: name, email, password (plain), role
     */
    public static function create(array $data): int
    {
        $token = bin2hex(random_bytes(32)); // 64-char verification token

        self::execute(
            'INSERT INTO users (name, email, password, role, email_verification_token)
             VALUES (?, ?, ?, ?, ?)',
            [
                $data['name'],
                $data['email'],
                password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
                $data['role'] ?? 'customer',
                $token,
            ]
        );

        return (int) self::lastInsertId();
    }

    /**
     * Mark a user's email as verified and clear the token.
     */
    public static function markEmailVerified(int $id): void
    {
        self::execute(
            'UPDATE users
             SET email_verified_at = NOW(), email_verification_token = NULL
             WHERE id = ?',
            [$id]
        );
    }

    /**
     * Update a user's password.
     */
    public static function updatePassword(int $id, string $plainPassword): void
    {
        self::execute(
            'UPDATE users SET password = ? WHERE id = ?',
            [
                password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 12]),
                $id,
            ]
        );
    }

    // -----------------------------------------------------------------------
    // Verification helpers
    // -----------------------------------------------------------------------

    /**
     * Check whether a given plain-text password matches the stored hash.
     */
    public static function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    /**
     * Return true if the user has verified their email.
     *
     * @param array<string, mixed> $user
     */
    public static function isVerified(array $user): bool
    {
        return !empty($user['email_verified_at']);
    }
}
