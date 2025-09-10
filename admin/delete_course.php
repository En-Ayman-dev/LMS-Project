<?php
require '../includes/database.php';
require '../includes/auth.php';

check_role('admin');

if (isset($_GET['id'])) {
    $course_id = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $course_id);

    if ($stmt->execute()) {
        header("Location: manage_courses.php");
        exit;
    } else {
        // يمكنك توجيه المستخدم إلى صفحة خطأ أو عرض رسالة مناسبة
        echo "خطأ: فشل حذف المادة. قد تكون هناك سجلات مرتبطة بهذه المادة (مثل طلاب مسجلين).";
        echo "<br><a href='manage_courses.php'>العودة إلى إدارة المواد</a>";
    }
} else {
    header("Location: manage_courses.php");
    exit;
}