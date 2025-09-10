<?php
require '../includes/database.php';
require '../includes/auth.php';

// التحقق من صلاحيات الطالب
check_role('student');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['id'];
    $assignment_id = intval($_POST['assignment_id']);
    $submission_type = $_POST['submission_type'];

    // التحقق من وجود تقديم سابق لهذا الواجب من قبل هذا الطالب
    $stmt_check = $conn->prepare("SELECT id FROM submissions WHERE student_id = ? AND assignment_id = ?");
    $stmt_check->bind_param("ii", $student_id, $assignment_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    // إذا كان هناك تقديم سابق، قم بحذفه لمنع التكرار
    if ($result_check->num_rows > 0) {
        $submission_id = $result_check->fetch_assoc()['id'];
        $stmt_delete = $conn->prepare("DELETE FROM submissions WHERE id = ?");
        $stmt_delete->bind_param("i", $submission_id);
        $stmt_delete->execute();
    }

    $submission_path = null;

    if ($submission_type === 'file') {
        // معالجة رفع الملف
        if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../upload/submissions/';
            $file_extension = pathinfo($_FILES['submission_file']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('submission_') . '.' . $file_extension;
            $submission_path = $upload_dir . $new_filename;

            if (!move_uploaded_file($_FILES['submission_file']['tmp_name'], $submission_path)) {
                echo "خطأ في رفع الملف.";
                exit;
            }
        } else {
            echo "❌ يرجى اختيار ملف.";
            exit;
        }
    } elseif ($submission_type === 'link') {
        // معالجة إرسال الرابط
        if (isset($_POST['submission_link']) && !empty($_POST['submission_link'])) {
            $submission_path = filter_var($_POST['submission_link'], FILTER_SANITIZE_URL);
        } else {
            echo "❌ يرجى إدخال رابط.";
            exit;
        }
    }

    // إدراج السجل الجديد
    $stmt_insert = $conn->prepare("INSERT INTO submissions (assignment_id, student_id, file_path) VALUES (?, ?, ?)");
    $stmt_insert->bind_param("iis", $assignment_id, $student_id, $submission_path);

    if ($stmt_insert->execute()) {
        header("Location: courses.php?course_id=" . $_GET['course_id']);
        exit;
    } else {
        echo "خطأ في تقديم الواجب: " . $conn->error;
    }
} else {
    header("Location: dashboard.php");
    exit;
}