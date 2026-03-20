<?php

declare(strict_types=1);

namespace Core;

/**
 * Auth — authentication state facade.
 *
 * Stores authenticated user data in the session.
 * Call Auth::login() after verifying credentials.
 * Call Auth::logout() to end the session.
 */
class Auth
{
    private const SESSION_KEY = '_auth_user';

    // -----------------------------------------------------------------------
    // Login / Logout
    // -----------------------------------------------------------------------

    /**
     * Log a user in.
     * Stores the user array in session and regenerates the session ID
     * to prevent session fixation attacks.
     *
     * @param array<string, mixed> $user  A row from the users table
     */
    public static function login(array $user): void
    {
        // Never store the hashed password in session
        unset($user['password'], $user['email_verification_token']);

        Session::regenerate();
        Session::set(self::SESSION_KEY, $user);
    }

    /**
     * Log the current user out and destroy the session.
     */
    public static function logout(): void
    {
        Session::destroy();
    }

    // -----------------------------------------------------------------------
    // State
    // -----------------------------------------------------------------------

    /**
     * Return the authenticated user array, or null if not authenticated.
     *
     * @return array<string, mixed>|null
     */
    public static function user(): ?array
    {
        $user = Session::get(self::SESSION_KEY);
        return is_array($user) ? $user : null;
    }

    /**
     * Is there an authenticated user?
     */
    public static function check(): bool
    {
        return self::user() !== null;
    }

    /**
     * Is the visitor NOT authenticated (guest)?
     */
    public static function guest(): bool
    {
        return !self::check();
    }

    /**
     * Return the authenticated user's ID, or null.
     */
    public static function id(): ?int
    {
        $user = self::user();
        return isset($user['id']) ? (int) $user['id'] : null;
    }

    /**
     * Return the authenticated user's role, or null.
     */
    public static function role(): ?string
    {
        return self::user()['role'] ?? null;
    }

    /**
     * Check whether the authenticated user has a specific role.
     */
    public static function hasRole(string $role): bool
    {
        return self::role() === $role;
    }

    /**
     * Refresh the session user from a freshly fetched DB row.
     * Useful after updating profile details.
     *
     * @param array<string, mixed> $user
     */
    public static function refresh(array $user): void
    {
        unset($user['password'], $user['email_verification_token']);
        Session::set(self::SESSION_KEY, $user);
    }
}
