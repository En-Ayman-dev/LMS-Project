<?php
require '../includes/database.php';
require '../includes/auth.php';

// التحقق من صلاحيات المعلم
check_role('teacher');

$assignment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$teacher_id = $_SESSION['id'];

if ($assignment_id === 0) {
    echo "❌ لم يتم تحديد الواجب.";
    exit;
}

// جلب بيانات الواجب الحالية والتأكد من أنه يخص المعلم الحالي
$stmt = $conn->prepare("
    SELECT a.*, c.id AS course_id, c.title AS course_title
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    WHERE a.id = ? AND c.teacher_id = ?
");
$stmt->bind_param("ii", $assignment_id, $teacher_id);
$stmt->execute();
$assignment_result = $stmt->get_result();

if ($assignment_result->num_rows === 0) {
    echo "❌ هذا الواجب غير موجود أو لا تملك صلاحية تعديله.";
    exit;
}
$assignment = $assignment_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $file_path = $assignment['file_path']; // احتفظ بالمسار الحالي

    // معالجة رفع الملف الجديد إذا تم إرفاقه
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/assignments/';
        $file_extension = pathinfo($_FILES['assignment_file']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid('assignment_') . '.' . $file_extension;
        $new_file_path = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $new_file_path)) {
            // حذف الملف القديم إذا كان موجودًا
            if ($file_path && file_exists($file_path)) {
                unlink($file_path);
            }
            $file_path = $new_file_path;
        } else {
            echo "خطأ في رفع الملف الجديد.";
            exit;
        }
    }

    $stmt_update = $conn->prepare("UPDATE assignments SET title = ?, description = ?, due_date = ?, file_path = ? WHERE id = ?");
    $stmt_update->bind_param("ssssi", $title, $description, $due_date, $file_path, $assignment_id);

    if ($stmt_update->execute()) {
        header("Location: my_courses.php?course_id=" . $assignment['course_id']);
        exit;
    } else {
        echo "خطأ في تحديث الواجب: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>تعديل الواجب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <?php require_once '../includes/teacher_navbar.php'; ?>

    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-header bg-warning text-dark">
                <h3 class="mb-0">✏ تعديل بيانات الواجب</h3>
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">عنوان الواجب</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($assignment['title']) ?>"
                            class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">وصف الواجب</label>
                        <textarea name="description" class="form-control"
                            rows="3"><?= htmlspecialchars($assignment['description']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تاريخ التسليم</label>
                        <input type="datetime-local" name="due_date"
                            value="<?= date('Y-m-d\TH:i', strtotime($assignment['due_date'])) ?>" class="form-control"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="assignmentFile" class="form-label">ملف الواجب (اترك فارغًا لعدم التغيير)</label>
                        <input type="file" class="form-control" id="assignmentFile" name="assignment_file">
                        <?php if (!empty($assignment['file_path'])): ?>
                            <small class="form-text text-muted">الملف الحالي: <a
                                    href="<?= htmlspecialchars($assignment['file_path']) ?>" target="_blank">عرض
                                    الملف</a></small>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-warning">💾 تحديث</button>
                    <a href="my_courses.php?course_id=<?= $assignment['course_id'] ?>" class="btn btn-secondary">🔙
                        رجوع</a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>