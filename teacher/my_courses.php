<?php
require '../includes/database.php';
require '../includes/auth.php';

// التحقق من صلاحيات المعلم
check_role('teacher');

// جلب وتأكيد معرف المادة من الرابط
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
if ($course_id === 0) {
    echo "❌ لم يتم تحديد المادة.";
    exit;
}

// جلب معرف المعلم الحالي الذي قام بتسجيل الدخول
$teacher_id = $_SESSION['id'];

// التحقق من وجود المادة وأنها مسندة للمعلم الحالي
$stmt_course = $conn->prepare("SELECT title FROM courses WHERE id = ? AND teacher_id = ?");
$stmt_course->bind_param("ii", $course_id, $teacher_id);
$stmt_course->execute();
$course_result = $stmt_course->get_result();
if ($course_result->num_rows === 0) {
    echo "❌ المادة غير موجودة أو غير مسندة إليك.";
    exit;
}
$course = $course_result->fetch_assoc();

// جلب الطلاب المسجلين
$stmt_students = $conn->prepare("SELECT u.id, u.name FROM enrollments e JOIN users u ON e.student_id = u.id WHERE e.course_id = ?");
$stmt_students->bind_param("i", $course_id);
$stmt_students->execute();
$students_result = $stmt_students->get_result();

// جلب الواجبات الخاصة بهذه المادة
$stmt_assignments = $conn->prepare("SELECT * FROM assignments WHERE course_id = ?");
$stmt_assignments->bind_param("i", $course_id);
$stmt_assignments->execute();
$assignments_result = $stmt_assignments->get_result();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>إدارة <?= htmlspecialchars($course['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php require_once '../includes/teacher_navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-primary">إدارة المادة: <?= htmlspecialchars($course['title']) ?></h2>
            <div>
                <span class="me-3">مرحباً، <?= $_SESSION['name'] ?> (<?= $_SESSION['role'] ?>)</span>
                <a href="../logout.php" class="btn btn-outline-danger btn-sm">🚪 تسجيل الخروج</a>
            </div>
        </div>

        <div class="mb-3">
            <a href="dashboard.php" class="btn btn-secondary">🔙 العودة إلى لوحة التحكم</a>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-lg mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">الطلاب المسجلون</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>اسم الطالب</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($students_result->num_rows > 0): ?>
                                    <?php while ($student = $students_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $student['id'] ?></td>
                                            <td><?= htmlspecialchars($student['name']) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="text-center">لا يوجد طلاب مسجلين في هذه المادة.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-lg mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">الواجبات</h5>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-success mb-3" data-bs-toggle="modal"
                            data-bs-target="#addAssignmentModal">➕ إضافة واجب جديد</button>
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>عنوان الواجب</th>
                                    <th>تاريخ التسليم</th>
                                    <th>العمليات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($assignments_result->num_rows > 0): ?>
                                    <?php while ($assignment = $assignments_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $assignment['id'] ?></td>
                                            <td><?= htmlspecialchars($assignment['title']) ?></td>
                                            <td><?= $assignment['due_date'] ?></td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <a href="view_assignment.php?id=<?= $assignment['id'] ?>"
                                                        class="btn btn-sm btn-info">عرض</a>
                                                    <a href="edit_assignment.php?id=<?= $assignment['id'] ?>"
                                                        class="btn btn-sm btn-warning">تعديل</a>
                                                    <a href="delete_assignment.php?id=<?= $assignment['id'] ?>"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">لا توجد واجبات لهذه المادة.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addAssignmentModal" tabindex="-1" aria-labelledby="addAssignmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="add_assignment_process.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="course_id" value="<?= $course_id ?>">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addAssignmentModalLabel">إضافة واجب جديد</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="assignmentTitle" class="form-label">عنوان الواجب</label>
                            <input type="text" class="form-control" id="assignmentTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="assignmentDescription" class="form-label">وصف الواجب</label>
                            <textarea class="form-control" id="assignmentDescription" name="description"
                                rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="assignmentDueDate" class="form-label">تاريخ التسليم</label>
                            <input type="datetime-local" class="form-control" id="assignmentDueDate" name="due_date"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="assignmentFile" class="form-label">ملف الواجب (اختياري)</label>
                            <input type="file" class="form-control" id="assignmentFile" name="assignment_file">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ الواجب</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>