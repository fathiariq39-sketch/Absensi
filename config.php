<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

$pdo = new PDO('mysql:host=localhost;dbname=db_absensi;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function login_required(array $roles = []) {
    if (empty($_SESSION['user'])) { header('Location: index.php'); exit; }
    if ($roles && !in_array($_SESSION['user']['hak_akses'], $roles)) {
        http_response_code(403); exit('Akses ditolak.');
    }
}

function flash($msg = null, $type = 'ok') {
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $type]; return; }
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f;
}

function csrf_field() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}
function csrf_check() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400); exit('Token tidak valid.');
    }
}
function tgl($d) { return date('d-m-Y', strtotime($d)); }
