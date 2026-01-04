<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Config\AppConfig;
use SQLite3;

final class AdminSessionRepository
{
    private SQLite3 $db;

    public function __construct(SQLite3 $db)
    {
        $this->db = $db;
    }

    public function hashPassword(string $password): string
    {
        return hash('sha256', AppConfig::ADMIN_PASSWORD_SALT . $password);
    }

    public function setAdminCookie(string $token): void
    {
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie(
            AppConfig::ADMIN_SESSION_COOKIE,
            $token,
            [
                'expires' => time() + AppConfig::ADMIN_SESSION_TTL,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure' => $secure,
            ]
        );
    }

    public function clearAdminCookie(): void
    {
        setcookie(
            AppConfig::ADMIN_SESSION_COOKIE,
            '',
            [
                'expires' => time() - 3600,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    public function createSession(string $ip): string
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $stmt = $this->db->prepare('INSERT INTO admin_sessions (token_hash, ip_addr, expires_at) VALUES (:hash, :ip, datetime(\'now\', :ttl))');
        $stmt->bindValue(':hash', $hash, SQLITE3_TEXT);
        $stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
        $stmt->bindValue(':ttl', '+' . AppConfig::ADMIN_SESSION_TTL . ' seconds', SQLITE3_TEXT);
        $stmt->execute();
        return $token;
    }

    public function deleteSession(string $token): void
    {
        $hash = hash('sha256', $token);
        $stmt = $this->db->prepare('DELETE FROM admin_sessions WHERE token_hash = :hash');
        $stmt->bindValue(':hash', $hash, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function isSessionValid(string $ip): bool
    {
        $token = $_COOKIE[AppConfig::ADMIN_SESSION_COOKIE] ?? '';
        if ($token === '') {
            return false;
        }
        $hash = hash('sha256', $token);
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM admin_sessions WHERE token_hash = :hash AND ip_addr = :ip AND expires_at >= datetime(\'now\')');
        $stmt->bindValue(':hash', $hash, SQLITE3_TEXT);
        $stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
        $res = $stmt->execute();
        $row = $res !== false ? $res->fetchArray(SQLITE3_NUM) : false;
        return $row !== false && (int) $row[0] > 0;
    }

    public function purgeExpired(): void
    {
        $this->db->exec('DELETE FROM admin_sessions WHERE expires_at < datetime(\'now\')');
    }
}
