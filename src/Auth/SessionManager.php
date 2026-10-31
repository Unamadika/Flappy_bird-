<?php

namespace App\Auth;

final class SessionManager
{
    private const INACTIVITY_TIMEOUT = 900;

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            "httponly" => true,
            "secure" => isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
            "samesite" => "Lax",
        ]);
        session_start();

        $lastActivity = $_SESSION["last_activity"] ?? null;
        if (
            $lastActivity !== null
            && (!is_int($lastActivity) || time() - $lastActivity >= self::INACTIVITY_TIMEOUT)
        ) {
            // Clear expired data and rotate the session ID before continuing anonymously.
            $_SESSION = [];
            session_regenerate_id(true);
        }

        $_SESSION["last_activity"] = time();
    }

    public static function requireLogin(): void
    {
        self::start();

        if (!self::isAuthenticated()) {
            header("Location: login.php");
            exit;
        }
    }

    public static function isAuthenticated(): bool
    {
        return isset($_SESSION["user_id"]);
    }

    // Generated with GitHub Copilot
    public static function csrfToken(): string
    {
        self::start();

        if (!isset($_SESSION["csrf_token"])) {
            $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
        }

        return $_SESSION["csrf_token"];
    }

    // Generated with GitHub Copilot
    public static function validateCsrfToken(?string $token): bool
    {
        self::start();

        return isset($_SESSION["csrf_token"])
            && is_string($token)
            && hash_equals($_SESSION["csrf_token"], $token);
    }

    public static function login(string $userId, string $username): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION["user_id"] = $userId;
        $_SESSION["username"] = $username;
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), "", [
                "expires" => time() - 42000,
                "path" => $params["path"],
                "domain" => $params["domain"],
                "secure" => $params["secure"],
                "httponly" => $params["httponly"],
                "samesite" => $params["samesite"] ?? "Lax",
            ]);
        }

        session_destroy();
    }
}
