<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once 'check_admin.php';

$id = (int)($_GET['id'] ?? 0);
$status = $_GET['status'] ?? '';

$allowed = ['new', 'processing', 'done'];
if ($id <= 0 || !in_array($status, $allowed)) {
    die('Некорректные данные');
}

$stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
$stmt->execute([$status, $id]);

header('Location: admin_orders.php');
exit;
