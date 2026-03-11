<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'success' => true,
    'message' => 'Test AJAX fonctionnel',
    'session_id' => session_id(),
    'session_status' => session_status(),
    'has_user' => isset($_SESSION['user'])
]);
?>