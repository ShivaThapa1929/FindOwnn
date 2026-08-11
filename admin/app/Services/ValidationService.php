<?php

namespace App\Services;

/**
 * ValidationService — Centralised server-side validation.
 * Returns errors array. Empty array = passed.
 */
class ValidationService
{
    private array $errors = [];

    public function required(mixed $value, string $field, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (is_string($value) && trim($value) === '') {
            $this->errors[$field] = "{$label} is required.";
        } elseif ($value === null || $value === '') {
            $this->errors[$field] = "{$label} is required.";
        }
        return $this;
    }

    public function minLength(string $value, string $field, int $min, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (strlen(trim($value)) < $min) {
            $this->errors[$field] = "{$label} must be at least {$min} characters.";
        }
        return $this;
    }

    public function maxLength(string $value, string $field, int $max, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (strlen(trim($value)) > $max) {
            $this->errors[$field] = "{$label} may not exceed {$max} characters.";
        }
        return $this;
    }

    public function email(string $value, string $field = 'email'): static
    {
        if (!filter_var(trim($value), FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Please enter a valid email address.';
        }
        return $this;
    }

    public function numeric(mixed $value, string $field, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (!is_numeric($value)) {
            $this->errors[$field] = "{$label} must be a number.";
        }
        return $this;
    }

    public function min(mixed $value, string $field, float $min, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (is_numeric($value) && (float)$value < $min) {
            $this->errors[$field] = "{$label} must be at least {$min}.";
        }
        return $this;
    }

    public function max(mixed $value, string $field, float $max, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (is_numeric($value) && (float)$value > $max) {
            $this->errors[$field] = "{$label} may not exceed {$max}.";
        }
        return $this;
    }

    public function in(mixed $value, string $field, array $allowed, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (!in_array($value, $allowed, true)) {
            $this->errors[$field] = "{$label} contains an invalid value.";
        }
        return $this;
    }

    public function date(string $value, string $field, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) || !strtotime($value)) {
            $this->errors[$field] = "{$label} must be a valid date (YYYY-MM-DD).";
        }
        return $this;
    }

    public function time(string $value, string $field, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value)) {
            $this->errors[$field] = "{$label} must be a valid time (HH:MM).";
        }
        return $this;
    }

    public function phone(string $value, string $field = 'phone'): static
    {
        $digits = preg_replace('/\D/', '', $value);
        if (strlen($digits) < 10 || strlen($digits) > 13) {
            $this->errors[$field] = 'Please enter a valid phone number.';
        }
        return $this;
    }

    public function url(string $value, string $field, string $label = ''): static
    {
        if ($value === '') return $this; // Optional URL
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[$field] = "{$label} must be a valid URL.";
        }
        return $this;
    }

    public function pincode(string $value, string $field = 'pincode'): static
    {
        if ($value === '') return $this;
        if (!preg_match('/^\d{6}$/', $value)) {
            $this->errors[$field] = 'Pincode must be exactly 6 digits.';
        }
        return $this;
    }

    public function unique(string $table, string $column, mixed $value, string $field, int $exceptId = 0): static
    {
        $db = \App\Core\Database::getInstance();
        $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = ?";
        $params = [$value];
        if ($exceptId > 0) {
            $sql .= ' AND id != ?';
            $params[] = $exceptId;
        }
        if ((int)$db->fetchColumn($sql, $params) > 0) {
            $this->errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' already exists.';
        }
        return $this;
    }

    public function custom(bool $condition, string $field, string $message): static
    {
        if (!$condition) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function fails(): bool  { return !empty($this->errors); }
    public function passes(): bool { return empty($this->errors); }
    public function errors(): array { return $this->errors; }

    public function firstError(): string
    {
        return array_values($this->errors)[0] ?? '';
    }

    /** Flash all errors to session and redirect back */
    public function flashAndRedirect(string $url): void
    {
        $_SESSION['validation_errors'] = $this->errors;
        $_SESSION['old_input']         = $_POST;
        \App\Core\Session::flash('error', $this->firstError());
        header("Location: {$url}");
        exit;
    }
}
