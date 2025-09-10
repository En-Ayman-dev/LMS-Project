<?php
require '../includes/database.php';
require '../includes/auth.php';

// التحقق من صلاحيات الطالب
check_role('student');

$student_id = $_SESSION['id'];

// جلب جميع الدرجات الخاصة بالطالب
$stmt = $conn->prepare("
    SELECT
        c.title AS course_title,
        a.title AS assignment_title,
        s.grade,
        s.submitted_at
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN courses c ON a.course_id = c.id
    WHERE s.student_id = ?
    ORDER BY c.title, a.title
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$grades_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>درجاتي</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<?php require_once '../includes/student_navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-primary">📝 درجاتي</h2>
            <div>
                <span class="me-3">مرحباً، <?= $_SESSION['name'] ?> (<?= $_SESSION['role'] ?>)</span>
                <a href="../logout.php" class="btn btn-outline-danger btn-sm">🚪 تسجيل الخروج</a>
            </div>
        </div>

        <div class="mb-3">
            <a href="dashboard.php" class="btn btn-secondary">🔙 العودة إلى لوحة التحكم</a>
        </div>

        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">سجل الدرجات</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>المادة</th>
                            <th>الواجب</th>
                            <th>الدرجة</th>
                            <th>تاريخ التقديم</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($grades_result->num_rows > 0): ?>
                            <?php while ($grade = $grades_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($grade['course_title']) ?></td>
                                    <td><?= htmlspecialchars($grade['assignment_title']) ?></td>
                                    <td><?= $grade['grade'] ?? 'لم يتم التقييم' ?></td>
                                    <td><?= $grade['submitted_at'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">لا توجد درجات لعرضها حاليًا.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>

</html>