<?php
require '../includes/database.php';
require '../includes/auth.php';

check_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = $_POST['title'];
    $description = $_POST['description'];
    $teacher_id  = $_POST['teacher_id'];

    $stmt = $conn->prepare("INSERT INTO courses (title, description, teacher_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $title, $description, $teacher_id);

    if ($stmt->execute()) {
        header("Location: manage_courses.php");
        exit;
    } else {
        echo "خطأ في إضافة المادة: " . $conn->error;
    }
} else {
    header("Location: manage_courses.php");
    exit;
}