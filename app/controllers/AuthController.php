<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Auth;
use Core\Csrf;
use Core\Session;
use App\Models\User;

/**
 * Handles all authentication endpoints:
 *   GET/POST /register
 *   GET/POST /login
 *   GET      /logout
 *   GET      /verify-email
 */
class AuthController extends Controller
{
    // -----------------------------------------------------------------------
    // Register
    // -----------------------------------------------------------------------

    public function showRegister(Request $request): void
    {
        $this->requireGuest();
        $this->render('auth/register', ['title' => 'Créer un compte'], 'auth');
    }

    public function register(Request $request): void
    {
        $this->requireGuest();
        Csrf::validate($request);

        $name            = trim($request->input('name', ''));
        $email           = strtolower(trim($request->input('email', '')));
        $password        = $request->input('password', '');
        $passwordConfirm = $request->input('password_confirm', '');

        // ----- Validation -----
        $errors = [];

        if ($name === '' || strlen($name) < 2) {
            $errors[] = 'Le nom doit comporter au moins 2 caractères.';
        }
        if (strlen($name) > 100) {
            $errors[] = 'Le nom ne peut pas dépasser 100 caractères.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse e-mail invalide.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit comporter au moins 8 caractères.';
        }
        if ($password !== $passwordConfirm) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }
        if (empty($errors) && User::findByEmail($email)) {
            $errors[] = 'Cette adresse e-mail est déjà utilisée.';
        }

        if (!empty($errors)) {
            // Store old input and errors, then redirect back
            Session::flash('errors', $errors);
            Session::flash('_old_input', ['name' => $name, 'email' => $email]);
            $this->redirect(url('register'));
        }

        // ----- Create user -----
        $userId = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => $password,
            'role'     => 'customer',
        ]);

        // ----- Send verification email (bonus) -----
        $user = User::findById($userId);
        if ($user && !empty($user['email_verification_token'])) {
            $this->sendVerificationEmail($user);
        }

        Session::flash('success', 'Compte créé ! Vérifiez votre boîte e-mail pour activer votre compte.');
        $this->redirect(url('verify-notice'));
    }

    // -----------------------------------------------------------------------
    // Login
    // -----------------------------------------------------------------------

    public function showLogin(Request $request): void
    {
        $this->requireGuest();
        $this->render('auth/login', ['title' => 'Connexion'], 'auth');
    }

    public function login(Request $request): void
    {
        $this->requireGuest();
        Csrf::validate($request);

        $email    = strtolower(trim($request->input('email', '')));
        $password = $request->input('password', '');

        // ----- Validation -----
        $errors = [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse e-mail invalide.';
        }
        if ($password === '') {
            $errors[] = 'Le mot de passe est requis.';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('_old_input', ['email' => $email]);
            $this->redirect(url('login'));
        }

        // ----- Credential check -----
        $user = User::findByEmail($email);

        if (!$user || !User::verifyPassword($password, $user['password'])) {
            Session::flash('errors', ['E-mail ou mot de passe incorrect.']);
            Session::flash('_old_input', ['email' => $email]);
            $this->redirect(url('login'));
        }

        // ----- Email verification gate -----
        if (!User::isVerified($user)) {
            Session::flash('errors', ['Veuillez vérifier votre adresse e-mail avant de vous connecter.']);
            Session::flash('_old_input', ['email' => $email]);
            $this->redirect(url('login'));
        }

        // ----- Authenticate -----
        Auth::login($user);
        Session::flash('success', 'Bienvenue, ' . $user['name'] . ' !');
        $this->redirect(url('dashboard'));
    }

    // -----------------------------------------------------------------------
    // Logout
    // -----------------------------------------------------------------------

    public function logout(Request $request): void
    {
        Auth::logout();
        $this->redirect(url('login'));
    }

    // -----------------------------------------------------------------------
    // Email verification
    // -----------------------------------------------------------------------

    public function verifyNotice(Request $request): void
    {
        $this->render('auth/verify-notice', ['title' => 'Vérification e-mail'], 'auth');
    }

    public function verifyEmail(Request $request): void
    {
        $token = trim($request->input('token', ''));

        if ($token === '') {
            Session::flash('errors', ['Lien de vérification invalide.']);
            $this->redirect(url('login'));
        }

        $user = User::findByVerificationToken($token);

        if (!$user) {
            Session::flash('errors', ['Ce lien de vérification est invalide ou a déjà été utilisé.']);
            $this->redirect(url('login'));
        }

        User::markEmailVerified((int) $user['id']);

        Session::flash('success', 'E-mail vérifié ! Vous pouvez maintenant vous connecter.');
        $this->redirect(url('login'));
    }

    // -----------------------------------------------------------------------
    // Private — Mailer
    // -----------------------------------------------------------------------

    /**
     * Send the verification email using PHP mail() or SMTP.
     * Works with Namecheap shared hosting default PHP mail().
     *
     * @param array<string, mixed> $user
     */
    private function sendVerificationEmail(array $user): void
    {
        $config     = require base_path('config/mail.php');
        $verifyUrl  = url('verify-email') . '?token=' . urlencode($user['email_verification_token']);
        $appName    = e(env('APP_NAME', 'Vanilla Groupe Madagascar'));
        $fromEmail  = $config['from_email'];
        $fromName   = $config['from_name'];

        $subject = "[$appName] Vérifiez votre adresse e-mail";

        $body = <<<HTML
        <!DOCTYPE html>
        <html lang="fr">
        <head><meta charset="UTF-8"><title>Vérification e-mail</title></head>
        <body style="font-family:sans-serif;max-width:600px;margin:auto;padding:24px;color:#1a1a1a;">
            <h2 style="color:#2d6a2d;">Bienvenue chez {$appName} 🌿</h2>
            <p>Merci de vous être inscrit. Cliquez sur le bouton ci-dessous pour vérifier votre adresse e-mail.</p>
            <p style="text-align:center;margin:32px 0;">
                <a href="{$verifyUrl}"
                   style="background:#2d6a2d;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;">
                    Vérifier mon e-mail
                </a>
            </p>
            <p style="color:#888;font-size:13px;">Ou copiez ce lien dans votre navigateur :<br>{$verifyUrl}</p>
            <hr style="border:none;border-top:1px solid #eee;margin:24px 0;">
            <p style="color:#aaa;font-size:12px;">Si vous n'avez pas créé de compte, ignorez cet e-mail.</p>
        </body>
        </html>
        HTML;

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";

        // Suppress mail errors silently in production (log manually if needed)
        @mail($user['email'], $subject, $body, $headers);
    }
}
