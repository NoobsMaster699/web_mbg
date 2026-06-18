<?php

function base_path(string $path = ''): string
{
    return dirname(__DIR__, 2) . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
}

function app_path(string $path = ''): string
{
    return base_path('app' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
}

function public_path(string $path = ''): string
{
    return base_path('public' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
}

function asset(string $path): string
{
    return '/assets/' . ltrim($path, '/');
}

function versioned_asset(string $path): string
{
    $file = public_path('assets/' . ltrim($path, '/'));
    $version = is_file($file) ? '?v=' . filemtime($file) : '';

    return asset($path) . $version;
}

function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? '';
    if (!$token || !hash_equals($_SESSION['_csrf'] ?? '', $token)) {
        flash_message('error', 'Akses Ditolak', 'CSRF token tidak valid. Muat ulang halaman lalu coba lagi.');
        http_response_code(419);
        exit('CSRF token tidak valid.');
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function auth_cookie_name(): string
{
    return 'spk_mbg_remember';
}

function auth_cookie_secret(): string
{
    return hash('sha256', getenv('APP_KEY') ?: 'spk-mbg-local-cookie-secret');
}

function auth_cookie_signature(int $id, string $username, string $role, int $expires): string
{
    return hash_hmac('sha256', $id . '|' . $username . '|' . $role . '|' . $expires, auth_cookie_secret());
}

function remember_user_cookie(array $user, int $days = 7): void
{
    $expires = time() + ($days * 24 * 60 * 60);
    $id = (int) ($user['id_user'] ?? 0);
    $username = (string) ($user['username'] ?? '');
    $role = (string) ($user['role'] ?? '');
    $signature = auth_cookie_signature($id, $username, $role, $expires);
    $payload = base64_encode(json_encode(compact('id', 'username', 'role', 'expires', 'signature')));

    setcookie(auth_cookie_name(), $payload, [
        'expires' => $expires,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function forget_user_cookie(): void
{
    setcookie(auth_cookie_name(), '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function restore_user_from_cookie(): bool
{
    if (empty($_COOKIE[auth_cookie_name()])) {
        return false;
    }

    $payload = json_decode(base64_decode((string) $_COOKIE[auth_cookie_name()], true) ?: '', true);
    if (!is_array($payload)) {
        forget_user_cookie();
        return false;
    }

    $id = (int) ($payload['id'] ?? 0);
    $username = (string) ($payload['username'] ?? '');
    $role = (string) ($payload['role'] ?? '');
    $expires = (int) ($payload['expires'] ?? 0);
    $signature = (string) ($payload['signature'] ?? '');

    $validSignature = hash_equals(auth_cookie_signature($id, $username, $role, $expires), $signature);
    if ($id < 1 || $expires < time() || !$validSignature) {
        forget_user_cookie();
        return false;
    }

    $stmt = Database::connection()->prepare('SELECT id_user, username, role FROM tbl_users WHERE id_user = ? AND username = ? LIMIT 1');
    $stmt->execute([$id, $username]);
    $user = $stmt->fetch();
    if (!$user) {
        forget_user_cookie();
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id_user' => (int) $user['id_user'],
        'username' => $user['username'],
        'role' => $user['role'],
    ];

    return true;
}

function auth_check(): bool
{
    return current_user() !== null || restore_user_from_cookie();
}

function flash_title_for(string $type): string
{
    return match ($type) {
        'success' => 'Berhasil',
        'error' => 'Gagal',
        'warning' => 'Peringatan',
        'info' => 'Informasi',
        default => 'Informasi',
    };
}

function flash_message(string $type, string $title, string $message): void
{
    $type = in_array($type, ['success', 'error', 'warning', 'info'], true) ? $type : 'info';
    $_SESSION['flash_message'] = compact('type', 'title', 'message');
}

function consume_flash_message(): ?array
{
    if (!empty($_SESSION['flash_message']) && is_array($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }

    foreach (['success', 'error', 'warning', 'info'] as $type) {
        if (!empty($_SESSION['flash'][$type])) {
            $message = [
                'type' => $type,
                'title' => flash_title_for($type),
                'message' => (string) $_SESSION['flash'][$type],
            ];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
    }

    return null;
}

function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        if (in_array($key, ['success', 'error', 'warning', 'info'], true)) {
            flash_message($key, flash_title_for($key), $value);
        } else {
            $_SESSION['flash'][$key] = $value;
        }
        return null;
    }

    $message = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $message;
}

function with_form_errors(array $errors, string $message = 'Periksa kembali input yang ditandai.', string $type = 'error', string $title = 'Validasi Gagal'): void
{
    $_SESSION['form_errors'] = $errors;
    $_SESSION['old_input'] = $_POST;
    flash_message($type, $title, $message);
}

function form_errors(): array
{
    if (!array_key_exists('_form_errors', $GLOBALS)) {
        $GLOBALS['_form_errors'] = $_SESSION['form_errors'] ?? [];
        unset($_SESSION['form_errors']);
    }

    return is_array($GLOBALS['_form_errors']) ? $GLOBALS['_form_errors'] : [];
}

function field_error(string $field): ?string
{
    $errors = form_errors();
    $value = $errors[$field] ?? null;
    if (is_array($value)) {
        return $value[0] ?? null;
    }
    return $value;
}

function has_field_error(string $field): bool
{
    return field_error($field) !== null;
}

function field_error_class(string $field): string
{
    return has_field_error($field) ? ' input-error' : '';
}

function field_error_attrs(string $field): string
{
    if (!has_field_error($field)) {
        return '';
    }

    return ' aria-invalid="true" aria-describedby="error-' . e(str_replace(['[', ']', '.', ' '], '-', $field)) . '"';
}

function field_error_html(string $field): string
{
    $error = field_error($field);
    if (!$error) {
        return '';
    }

    $id = 'error-' . str_replace(['[', ']', '.', ' '], '-', $field);
    return '<span class="field-error" id="' . e($id) . '">' . e($error) . '</span>';
}

function old_input_data(): array
{
    if (!array_key_exists('_old_input', $GLOBALS)) {
        $GLOBALS['_old_input'] = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);
    }

    return is_array($GLOBALS['_old_input']) ? $GLOBALS['_old_input'] : [];
}

function old(string $field, mixed $default = ''): mixed
{
    $old = old_input_data();
    return $old[$field] ?? $default;
}

function render_alert(array $flash): void
{
    $type = in_array($flash['type'] ?? 'info', ['success', 'error', 'warning', 'info'], true) ? $flash['type'] : 'info';
    $title = $flash['title'] ?? flash_title_for($type);
    $message = $flash['message'] ?? '';
    require app_path('views/components/alert.php');
}
