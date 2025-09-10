<?php
require '../includes/database.php';
require '../includes/auth.php';

// التحقق من صلاحيات المعلم
check_role('teacher');

if (isset($_GET['id'])) {
    $assignment_id = intval($_GET['id']);
    $teacher_id = $_SESSION['id'];

    // التحقق من أن الواجب يخص المعلم الحالي
    $stmt_check = $conn->prepare("
        SELECT c.id
        FROM assignments a
        JOIN courses c ON a.course_id = c.id
        WHERE a.id = ? AND c.teacher_id = ?
    ");
    $stmt_check->bind_param("ii", $assignment_id, $teacher_id);
    $stmt_check->execute();
    $course_result = $stmt_check->get_result();

    if ($course_result->num_rows === 0) {
        echo "❌ لا تملك صلاحية حذف هذا الواجب.";
        exit;
    }

    // جلب مسار الملف لحذفه من الخادم
    $stmt_file = $conn->prepare("SELECT file_path FROM assignments WHERE id = ?");
    $stmt_file->bind_param("i", $assignment_id);
    $stmt_file->execute();
    $file_path = $stmt_file->get_result()->fetch_assoc()['file_path'];

    // حذف التقديمات المرتبطة بالواجب أولاً
    $stmt_delete_submissions = $conn->prepare("DELETE FROM submissions WHERE assignment_id = ?");
    $stmt_delete_submissions->bind_param("i", $assignment_id);
    $stmt_delete_submissions->execute();

    // حذف الواجب
    $stmt_delete_assignment = $conn->prepare("DELETE FROM assignments WHERE id = ?");
    $stmt_delete_assignment->bind_param("i", $assignment_id);

    if ($stmt_delete_assignment->execute()) {
        // حذف الملف من الخادم بعد حذف السجل من قاعدة البيانات
        if ($file_path && file_exists($file_path)) {
            unlink($file_path);
        }
        $course_id = $course_result->fetch_assoc()['id']; // لاستخدامه في التوجيه
        header("Location: my_courses.php?course_id=" . $course_id);
        exit;
    } else {
        echo "خطأ في حذف الواجب: " . $conn->error;
    }
} else {
    header("Location: dashboard.php");
    exit;
}