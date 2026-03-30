<?php
// api/config.php
require_once __DIR__ . '/../config/koneksi.php';

// Define a simple API Key for authentication.
// IMPORTANT: Change this key for production environments!
define('API_KEY', 'ARSIP-DIGITAL-KEY-2024');

// Set headers for JSON response
header('Content-Type: application/json');

/**
 * Helper function to send JSON response
 */
function send_response($status_code, $data) {
    http_response_code($status_code);
    echo json_encode($data);
    exit;
}

/**
 * Simple Authentication Check
 */
function check_auth() {
    $provided_key = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? '';
    if ($provided_key !== API_KEY) {
        send_response(401, ['error' => 'Unauthorized. Invalid or missing API Key.']);
    }
}