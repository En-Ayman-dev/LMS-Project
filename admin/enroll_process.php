<?php
require '../includes/database.php';
require '../includes/auth.php';

// التحقق من صلاحيات الأدمن
check_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = intval($_POST['student_id']);
    $course_id = intval($_POST['course_id']);

    // التحقق من عدم وجود تسجيل مسبق
    $stmt_check = $conn->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
    $stmt_check->bind_param("ii", $student_id, $course_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        echo "❌ هذا الطالب مسجل بالفعل في هذه المادة.";
    } else {
        // إذا لم يكن التسجيل موجوداً، قم بإضافته
        $stmt_insert = $conn->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
        $stmt_insert->bind_param("ii", $student_id, $course_id);

        if ($stmt_insert->execute()) {
            header("Location: manage_enrollments.php");
            exit;
        } else {
            echo "خطأ في التسجيل: " . $conn->error;
        }
    }
} else {
    header("Location: manage_enrollments.php");
    exit;
}