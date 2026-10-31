<?php

// File purpose: end the player's session and return to the login page.
require_once dirname(__DIR__) . "/src/Auth/SessionManager.php";

// Clear the session data and its browser cookie before redirecting.
\App\Auth\SessionManager::logout();
header("Location: login.php");
exit;
