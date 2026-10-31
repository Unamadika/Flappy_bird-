<?php

require_once dirname(__DIR__) . "/src/Auth/SessionManager.php";

\App\Auth\SessionManager::logout();
header("Location: login.php");
exit;
