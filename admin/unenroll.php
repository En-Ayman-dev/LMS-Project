<?php
require '../includes/database.php';
require '../includes/auth.php';

// التحقق من صلاحيات الأدمن
check_role('admin');

if (isset($_GET['id'])) {
    $enrollment_id = intval($_GET['id']);

    // حذف السجل من جدول enrollments
    $stmt = $conn->prepare("DELETE FROM enrollments WHERE id = ?");
    $stmt->bind_param("i", $enrollment_id);

    if ($stmt->execute()) {
        header("Location: manage_enrollments.php");
        exit;
    } else {
        echo "خطأ في إلغاء التسجيل: " . $conn->error;
    }
} else {
    header("Location: manage_enrollments.php");
    exit;
}