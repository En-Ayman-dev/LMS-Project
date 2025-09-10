<?php
require '../includes/database.php';
require '../includes/auth.php';

// التحقق من صلاحيات المعلم
check_role('teacher');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_id = $_POST['submission_id'];
    $grade = $_POST['grade'];
    $assignment_id = $_POST['assignment_id']; // للحاجة في إعادة التوجيه

    // تحقق أمني: التأكد من أن التقديم يتبع لواجب يخص المعلم الحالي
    $stmt_check = $conn->prepare("
        SELECT s.id 
        FROM submissions s
        JOIN assignments a ON s.assignment_id = a.id
        JOIN courses c ON a.course_id = c.id
        WHERE s.id = ? AND c.teacher_id = ?
    ");
    $stmt_check->bind_param("ii", $submission_id, $_SESSION['id']);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // إذا كان التحقق صحيحاً، قم بتحديث الدرجة
        $stmt_update = $conn->prepare("UPDATE submissions SET grade = ? WHERE id = ?");
        $stmt_update->bind_param("di", $grade, $submission_id);

        if ($stmt_update->execute()) {
            // إعادة التوجيه إلى صفحة مراجعة الواجب
            header("Location: view_assignment.php?id=" . $assignment_id);
            exit;
        } else {
            echo "خطأ في تحديث الدرجة: " . $conn->error;
        }
    } else {
        echo "❌ لا تملك صلاحية تقييم هذا الواجب.";
    }
} else {
    // إذا لم يتم الإرسال عبر POST، أعد التوجيه
    header("Location: dashboard.php");
    exit;
}