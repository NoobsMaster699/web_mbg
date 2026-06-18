<?php

abstract class Controller
{
    private ?PDO $db = null;

    public function __construct()
    {
    }

    public function __get(string $name): mixed
    {
        if ($name === 'db') {
            return $this->connection();
        }

        trigger_error('Undefined property: ' . static::class . '::$' . $name, E_USER_NOTICE);
        return null;
    }

    private function connection(): PDO
    {
        if (!$this->db instanceof PDO) {
            $this->db = Database::connection();
        }

        return $this->db;
    }

    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require app_path('views/' . $view . '.php');
        $content = ob_get_clean();
        require app_path('views/layouts/' . $layout . '.php');
    }

    protected function requireAuth(): void
    {
        if (!auth_check() || (current_user()['role'] ?? '') !== 'admin') {
            flash_message('warning', 'Login Diperlukan', 'Silakan login terlebih dahulu.');
            redirect('login');
        }
    }

    protected function logActivity(string $modul, string $aksi, string $deskripsi, string $status = 'Berhasil'): void
    {
        $stmt = $this->connection()->prepare('INSERT INTO tbl_aktivitas (user_id, modul, aksi, deskripsi, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([current_user()['id_user'] ?? null, $modul, $aksi, $deskripsi, $status]);
    }
}
