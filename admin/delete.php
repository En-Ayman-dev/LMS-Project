<?php
require '../includes/database.php';
require '../includes/auth.php';

check_role('admin');

$id = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM users WHERE id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: dashboard.php");
    exit;
} else {
    echo "خطأ: " . $conn->error;
}