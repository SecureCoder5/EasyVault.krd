<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Require user to be logged in
 */
function requireLogin(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
}

/**
 * Require verified email
 */
function requireVerified(): void
{
    if (empty($_SESSION['user_verified']) || $_SESSION['user_verified'] !== true) {
        exit('Email verification required');
    }
}

/**
 * Require admin role
 */
function requireAdmin(): void
{
    requireLogin();

    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Access denied');
    }
}
