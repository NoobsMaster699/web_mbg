<?php

class AuthController extends Controller
{
    public function login(): void
    {
        if (auth_check()) {
            redirect('dashboard');
        }
        $this->view('auth/login', ['title' => 'Login'], 'guest');
    }

    public function authenticate(): void
    {
        verify_csrf();
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = [];

        if ($username === '') {
            $errors['username'] = 'Username wajib diisi.';
        }
        if ($password === '') {
            $errors['password'] = 'Password wajib diisi.';
        }
        if ($errors) {
            with_form_errors($errors, 'Lengkapi username dan password untuk masuk.');
            redirect('login');
        }

        $stmt = $this->db->prepare('SELECT * FROM tbl_users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            with_form_errors(['username' => 'Username atau password tidak sesuai.', 'password' => 'Username atau password tidak sesuai.'], 'Username atau password tidak sesuai.');
            redirect('login');
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id_user' => (int) $user['id_user'],
            'username' => $user['username'],
            'role' => $user['role'],
        ];
        if (!empty($_POST['remember'])) {
            remember_user_cookie($_SESSION['user']);
        } else {
            forget_user_cookie();
        }
        $this->logActivity('Autentikasi', 'Login', 'Login berhasil');
        flash_message('success', 'Berhasil', 'Login berhasil. Selamat datang di dashboard SPK MBG.');
        redirect('dashboard');
    }

    public function logout(): void
    {
        verify_csrf();
        $this->logActivity('Autentikasi', 'Logout', 'Logout berhasil');
        $_SESSION = [];
        forget_user_cookie();
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            $cookieOptions = [
                'expires' => time() - 3600,
                'path' => $params['path'] ?? '/',
                'secure' => (bool) ($params['secure'] ?? false),
                'httponly' => (bool) ($params['httponly'] ?? true),
                'samesite' => $params['samesite'] ?? 'Lax',
            ];
            if (!empty($params['domain'])) {
                $cookieOptions['domain'] = $params['domain'];
            }
            setcookie(session_name(), '', $cookieOptions);
        }
        session_destroy();
        redirect('login');
    }
}
