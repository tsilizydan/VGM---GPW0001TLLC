<?php

declare(strict_types=1);

namespace Core;

/**
 * CSRF Protection.
 *
 * Generates a per-session token and validates it on every POST request.
 * Use Csrf::field() inside every form to inject the hidden input.
 */
class Csrf
{
    private const TOKEN_KEY  = '_csrf_token';
    private const FIELD_NAME = '_token';

    // -----------------------------------------------------------------------
    // Token generation
    // -----------------------------------------------------------------------

    /**
     * Return the current CSRF token, generating one if it doesn't exist.
     */
    public static function token(): string
    {
        if (!Session::has(self::TOKEN_KEY)) {
            Session::set(self::TOKEN_KEY, bin2hex(random_bytes(32)));
        }

        return (string) Session::get(self::TOKEN_KEY);
    }

    /**
     * Regenerate the token (call after successful form submission).
     */
    public static function regenerate(): void
    {
        Session::set(self::TOKEN_KEY, bin2hex(random_bytes(32)));
    }

    // -----------------------------------------------------------------------
    // HTML output
    // -----------------------------------------------------------------------

    /**
     * Return an HTML hidden input containing the CSRF token.
     */
    public static function field(): string
    {
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            self::FIELD_NAME,
            htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8')
        );
    }

    // -----------------------------------------------------------------------
    // Validation
    // -----------------------------------------------------------------------

    /**
     * Validate the CSRF token from the current request.
     * Aborts with HTTP 419 if the token is missing or invalid.
     */
    public static function validate(Request $request): void
    {
        if (!$request->isPost()) {
            return; // Only validate POST requests
        }

        $submitted = $_POST[self::FIELD_NAME] ?? '';
        $expected  = Session::get(self::TOKEN_KEY, '');

        if (
            $submitted === ''
            || $expected === ''
            || !hash_equals($expected, $submitted)
        ) {
            http_response_code(419);
            echo '<h1>419 – Token Expired</h1>';
            echo '<p>Your session token is invalid. <a href="javascript:history.back()">Go back</a>.</p>';
            exit;
        }

        // Rotate token after every successful POST
        self::regenerate();
    }
}
