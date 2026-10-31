<?php

// File purpose: start secure sessions and manage login, timeout, and CSRF checks.
namespace App\Auth;

class SessionManager
{
    // Expire a session after 15 minutes without a request.
    private const INACTIVITY_TIMEOUT = 900;

    // Start the PHP session and clear it if the player was inactive too long.
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

        if (isset($_SESSION["last_activity"])) {
            $lastActivity = $_SESSION["last_activity"];

            if (
                !is_int($lastActivity)
                || time() - $lastActivity >= self::INACTIVITY_TIMEOUT
            ) {
                // Remove old login and security data and change the session ID.
                $_SESSION = [];
                session_regenerate_id(true);
            }
        }

        // Record this request so the next request can measure inactivity.
        $_SESSION["last_activity"] = time();
    }

    // Send page visitors without a login back to the login form.
    public static function requireLogin(): void
    {
        self::start();

        if (!self::isAuthenticated()) {
            header("Location: login.php");
            exit;
        }
    }

    // Return true only when this session contains a logged-in user ID.
    public static function isAuthenticated(): bool
    {
        return isset($_SESSION["user_id"]);
    }

    // Generated with GitHub Copilot
    // Create a random CSRF token once and keep it in the session.
    public static function csrfToken(): string
    {
        self::start();

        if (!isset($_SESSION["csrf_token"])) {
            $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
        }

        return $_SESSION["csrf_token"];
    }

    // Generated with GitHub Copilot
    // Compare submitted and stored tokens without leaking timing information.
    public static function validateCsrfToken($token): bool
    {
        self::start();

        if (!isset($_SESSION["csrf_token"]) || !is_string($token)) {
            return false;
        }

        return hash_equals($_SESSION["csrf_token"], $token);
    }

    // Store the player identity after regenerating the session ID.
    public static function login(string $userId, string $username): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION["user_id"] = $userId;
        $_SESSION["username"] = $username;
        $_SESSION["last_activity"] = time();
    }

    // Clear session data, remove its cookie, and end the session.
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
