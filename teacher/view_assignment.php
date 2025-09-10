<?php
require '../includes/database.php';
require '../includes/auth.php';

// التحقق من صلاحيات المعلم
check_role('teacher');

// جلب وتأكيد معرف الواجب
$assignment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($assignment_id === 0) {
    echo "❌ لم يتم تحديد الواجب.";
    exit;
}

// جلب تفاصيل الواجب والتأكد من أنه يخص المعلم الحالي
$stmt_assignment = $conn->prepare("
    SELECT a.*, c.title AS course_title 
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    WHERE a.id = ? AND c.teacher_id = ?
");
$stmt_assignment->bind_param("ii", $assignment_id, $_SESSION['id']);
$stmt_assignment->execute();
$assignment_result = $stmt_assignment->get_result();
if ($assignment_result->num_rows === 0) {
    echo "❌ هذا الواجب غير موجود أو لا تملك صلاحية الوصول إليه.";
    exit;
}
$assignment = $assignment_result->fetch_assoc();

// جلب جميع تقديمات الطلاب لهذا الواجب
$stmt_submissions = $conn->prepare("
    SELECT s.*, u.name as student_name 
    FROM submissions s
    JOIN users u ON s.student_id = u.id
    WHERE s.assignment_id = ?
");
$stmt_submissions->bind_param("i", $assignment_id);
$stmt_submissions->execute();
$submissions_result = $stmt_submissions->get_result();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>مراجعة الواجب: <?= htmlspecialchars($assignment['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-primary">مراجعة الواجب: <?= htmlspecialchars($assignment['title']) ?></h2>
            <div>
                <span class="me-3">مرحباً، <?= $_SESSION['name'] ?> (<?= $_SESSION['role'] ?>)</span>
                <a href="../logout.php" class="btn btn-outline-danger btn-sm">🚪 تسجيل الخروج</a>
            </div>
        </div>

        <div class="mb-3">
            <a href="my_courses.php?course_id=<?= $assignment['course_id'] ?>" class="btn btn-secondary">🔙 العودة إلى
                المادة</a>
        </div>

        <div class="card shadow-lg mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">تفاصيل الواجب</h5>
            </div>
            <div class="card-body">
                <p><strong>المادة:</strong> <?= htmlspecialchars($assignment['course_title']) ?></p>
                <p><strong>الوصف:</strong> <?= nl2br(htmlspecialchars($assignment['description'])) ?></p>
                <p><strong>تاريخ التسليم:</strong> <?= $assignment['due_date'] ?></p>
            </div>
        </div>

        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">تقديمات الطلاب</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>اسم الطالب</th>
                            <th>تاريخ التقديم</th>
                            <th>الملف</th>
                            <th>الدرجة</th>
                            <th>العملية</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($submissions_result->num_rows > 0): ?>
                            <?php while ($submission = $submissions_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($submission['student_name']) ?></td>
                                    <td><?= $submission['submitted_at'] ?></td>
                                    <td>
                                        <?php if (!empty($submission['file_path'])): ?>
                                            <a href="<?= htmlspecialchars($submission['file_path']) ?>" target="_blank">عرض
                                                الملف</a>
                                        <?php else: ?>
                                            لا يوجد ملف
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $submission['grade'] ?? 'لم يتم التقييم' ?></td>
                                    <td>
                                        <form action="grade_submission.php" method="POST" class="d-flex">
                                            <input type="hidden" name="submission_id" value="<?= $submission['id'] ?>">
                                            <input type="hidden" name="assignment_id" value="<?= $assignment_id ?>">
                                            <input type="number" name="grade" class="form-control form-control-sm me-2"
                                                style="width: 80px;" min="0" max="100" step="0.01" placeholder="الدرجة"
                                                required>
                                            <button type="submit" class="btn btn-sm btn-success">حفظ</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">لا توجد تقديمات لهذا الواجب حتى الآن.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>