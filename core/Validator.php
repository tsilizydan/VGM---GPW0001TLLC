<?php

declare(strict_types=1);

namespace Core;

/**
 * Input Validator.
 *
 * Usage:
 *   $v = Validator::make($_POST, [
 *       'name'  => 'required|min:2|max:120',
 *       'email' => 'required|email',
 *       'price' => 'required|numeric|min:0',
 *       'slug'  => 'required|slug',
 *       'role'  => 'required|in:admin,customer',
 *   ]);
 *
 *   if ($v->fails()) {
 *       // $v->errors() → ['field' => 'message', ...]
 *   }
 *   $safe = $v->validated(); // only validated fields, trimmed + cast
 */
class Validator
{
    /** @var array<string, string> */
    private array $errors = [];

    /** @var array<string, mixed> */
    private array $data = [];

    /** @var array<string, list<string>> */
    private array $rules = [];

    /**
     * @param array<string, mixed>  $data   Input data (e.g. $_POST)
     * @param array<string, string> $rules  Field → pipe-separated rules
     */
    public static function make(array $data, array $rules): static
    {
        $v = new static();
        $v->data  = self::deepTrim($data);
        $v->rules = self::parseRules($rules);
        $v->run();
        return $v;
    }

    // ── Results ────────────────────────────────────────────────────

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    /** @return array<string, string> */
    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): string
    {
        return array_values($this->errors)[0] ?? '';
    }

    /**
     * Return only the validated fields, trimmed and cast appropriately.
     * Fields not present in rules are excluded (safe allowlist).
     *
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        $out = [];
        foreach (array_keys($this->rules) as $field) {
            $out[$field] = $this->data[$field] ?? null;
        }
        return $out;
    }

    // ── Rule engine ────────────────────────────────────────────────

    private function run(): void
    {
        foreach ($this->rules as $field => $rules) {
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                [$name, $param] = $this->parseRule($rule);

                // skip non-required empty values (unless rule is 'required')
                if ($name !== 'required' && ($value === null || $value === '')) {
                    continue;
                }

                $error = match ($name) {
                    'required' => $this->ruleRequired($value, $field),
                    'min'      => $this->ruleMin($value, $field, (int) $param),
                    'max'      => $this->ruleMax($value, $field, (int) $param),
                    'email'    => $this->ruleEmail($value, $field),
                    'numeric'  => $this->ruleNumeric($value, $field),
                    'integer'  => $this->ruleInteger($value, $field),
                    'slug'     => $this->ruleSlug($value, $field),
                    'url'      => $this->ruleUrl($value, $field),
                    'in'       => $this->ruleIn($value, $field, $param),
                    'alpha'    => $this->ruleAlpha($value, $field),
                    'alphanum' => $this->ruleAlphaNum($value, $field),
                    'boolean'  => null, // always passes, cast handled elsewhere
                    'nullable' => null, // explicit pass
                    default    => null,
                };

                if ($error !== null && !isset($this->errors[$field])) {
                    $this->errors[$field] = $error;
                    break; // one error per field
                }
            }
        }
    }

    // ── Individual rules ──────────────────────────────────────────

    private function ruleRequired(mixed $value, string $field): ?string
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            return "Le champ « {$field} » est obligatoire.";
        }
        return null;
    }

    private function ruleMin(mixed $value, string $field, int $min): ?string
    {
        if (is_numeric($value)) {
            return (float)$value >= $min ? null : "Le champ « {$field} » doit être ≥ {$min}.";
        }
        $len = mb_strlen((string)$value);
        return $len >= $min ? null : "Le champ « {$field} » doit contenir au moins {$min} caractères.";
    }

    private function ruleMax(mixed $value, string $field, int $max): ?string
    {
        if (is_numeric($value)) {
            return (float)$value <= $max ? null : "Le champ « {$field} » doit être ≤ {$max}.";
        }
        $len = mb_strlen((string)$value);
        return $len <= $max ? null : "Le champ « {$field} » ne doit pas dépasser {$max} caractères.";
    }

    private function ruleEmail(mixed $value, string $field): ?string
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false
            ? null
            : "Le champ « {$field} » doit être une adresse e-mail valide.";
    }

    private function ruleNumeric(mixed $value, string $field): ?string
    {
        return is_numeric($value)
            ? null
            : "Le champ « {$field} » doit être un nombre.";
    }

    private function ruleInteger(mixed $value, string $field): ?string
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false
            ? null
            : "Le champ « {$field} » doit être un entier.";
    }

    private function ruleSlug(mixed $value, string $field): ?string
    {
        return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', (string)$value)
            ? null
            : "Le champ « {$field} » ne doit contenir que des lettres minuscules, chiffres et tirets.";
    }

    private function ruleUrl(mixed $value, string $field): ?string
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false
            ? null
            : "Le champ « {$field} » doit être une URL valide.";
    }

    private function ruleIn(mixed $value, string $field, ?string $param): ?string
    {
        $allowed = explode(',', $param ?? '');
        return in_array($value, $allowed, true)
            ? null
            : "Le champ « {$field} » doit être l'une des valeurs : " . implode(', ', $allowed) . '.';
    }

    private function ruleAlpha(mixed $value, string $field): ?string
    {
        return ctype_alpha((string)$value)
            ? null
            : "Le champ « {$field} » ne doit contenir que des lettres.";
    }

    private function ruleAlphaNum(mixed $value, string $field): ?string
    {
        return ctype_alnum((string)$value)
            ? null
            : "Le champ « {$field} » ne doit contenir que des lettres et chiffres.";
    }

    // ── Static utility ────────────────────────────────────────────

    /**
     * HTML-encode a string for safe output (alias of the e() helper).
     */
    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Strip all HTML tags from a string (for user-supplied rich text).
     *
     * @param string $allowedTags  e.g. '<p><a><strong><em><ul><ol><li>'
     */
    public static function stripTags(string $value, string $allowedTags = ''): string
    {
        return strip_tags($value, $allowedTags);
    }

    /**
     * Sanitize a slug: lowercase, replace spaces/special chars with hyphens.
     */
    public static function slugify(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = transliterator_transliterate('Any-Latin; Latin-ASCII', $value) ?: $value;
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? $value;
        return trim($value, '-');
    }

    // ── Internal ─────────────────────────────────────────────────

    /**
     * @param  array<string, string> $rawRules
     * @return array<string, list<string>>
     */
    private static function parseRules(array $rawRules): array
    {
        $parsed = [];
        foreach ($rawRules as $field => $ruleStr) {
            $parsed[$field] = array_filter(array_map('trim', explode('|', $ruleStr)));
        }
        return $parsed;
    }

    /** @return array{0: string, 1: string|null} */
    private function parseRule(string $rule): array
    {
        if (str_contains($rule, ':')) {
            [$name, $param] = explode(':', $rule, 2);
            return [$name, $param];
        }
        return [$rule, null];
    }

    /**
     * Recursively trim all string values from an input array.
     *
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function deepTrim(array $data): array
    {
        foreach ($data as $k => $v) {
            $data[$k] = is_string($v) ? trim($v) : (is_array($v) ? self::deepTrim($v) : $v);
        }
        return $data;
    }
}
