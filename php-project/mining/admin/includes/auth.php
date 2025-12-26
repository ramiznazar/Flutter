<?php
/**
 * Authentication Helper for Admin Panel
 * This file handles session management and authentication checks
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if admin is logged in
 * Redirects to login page if not authenticated
 */
function requireAuth() {
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_username'])) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Check if admin is already logged in
 * Redirects to dashboard if already authenticated
 */
function requireGuest() {
    if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_username'])) {
        header('Location: index.php');
        exit();
    }
}

/**
 * Get current admin ID
 */
function getAdminId() {
    return isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;
}

/**
 * Get current admin username
 */
function getAdminUsername() {
    return isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : null;
}

/**
 * Get current admin name
 */
function getAdminName() {
    return isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : null;
}

/**
 * Get current admin email
 */
function getAdminEmail() {
    return isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : null;
}

/**
 * Check if user is logged in (returns boolean)
 */
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}


