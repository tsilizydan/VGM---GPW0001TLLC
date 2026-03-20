<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Controller;
use Core\Request;
use Core\Model;
use Core\Session;
use Core\Csrf;

/**
 * Admin — Website settings (stored in DB `settings` table).
 */
class SettingsController extends Controller
{
    public function index(Request $request): void
    {
        $this->requireAuth(); $this->requireRole('admin');

        $this->render('admin/settings/index', [
            'title'    => 'Paramètres',
            'settings' => $this->allSettings(),
        ], 'admin');
    }

    public function update(Request $request): void
    {
        $this->requireAuth(); $this->requireRole('admin');
        if (!Csrf::validate($request->input('_token', ''))) \Core\Response::abort(403);

        $fields = [
            'site_name', 'site_tagline', 'site_email', 'site_phone',
            'site_address', 'site_facebook', 'site_instagram', 'site_twitter',
            'smtp_host', 'smtp_port', 'smtp_user',
            'shipping_free_over', 'tax_rate',
            'maintenance_mode',
        ];

        foreach ($fields as $key) {
            $val = $request->input($key, '');
            // SMTP password only saved if non-empty
            if ($key === 'smtp_password' && $val === '') continue;
            self::setSetting($key, $val);
        }

        // Handle SMTP password separately
        $smtpPw = $request->input('smtp_password', '');
        if ($smtpPw !== '') self::setSetting('smtp_password', $smtpPw);

        Session::flash('success', 'Paramètres enregistrés.');
        header('Location: ' . locale_url('admin/settings')); exit;
    }

    // ── Helpers ─────────────────────────────────────────────────

    public static function allSettings(): array
    {
        try {
            $rows = Model::rawQuery('SELECT `key`, `value` FROM settings');
            return array_column($rows, 'value', 'key');
        } catch (\Throwable) {
            return [];
        }
    }

    public static function get(string $key, string $default = ''): string
    {
        $settings = self::allSettings();
        return (string) ($settings[$key] ?? $default);
    }

    private static function setSetting(string $key, string $value): void
    {
        Model::rawQuery(
            'INSERT INTO settings (`key`, `value`) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
            [$key, $value]
        );
    }
}
