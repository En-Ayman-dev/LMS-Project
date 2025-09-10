<?php

require '../includes/database.php';
require '../includes/auth.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
check_role('teacher');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = intval($_POST['course_id']);
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];

    $file_path = null;

    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../upload/assignments/';
        $file_extension = pathinfo($_FILES['assignment_file']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid('assignment_') . '.' . $file_extension;
        $file_path = $upload_dir . $new_filename;

        // نقل الملف المرفوع
        if (!move_uploaded_file($_FILES['assignment_file']['tmp_name'], $file_path)) {
            echo "خطأ في رفع الملف.";
            exit;
        }
    }

    // استخدام prepared statement لإضافة الواجب
    $stmt = $conn->prepare("INSERT INTO assignments (course_id, title, description, due_date, file_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $course_id, $title, $description, $due_date, $file_path);

    if ($stmt->execute()) {
        header("Location: my_courses.php?course_id=" . $course_id);
        exit;
    } else {
        echo "خطأ في إضافة الواجب: " . $conn->error;
    }
} else {
    header("Location: dashboard.php");
    exit;
}