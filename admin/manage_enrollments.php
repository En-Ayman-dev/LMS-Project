<?php
require '../includes/database.php';
require '../includes/auth.php';

// التحقق من صلاحيات الأدمن
check_role('admin');

// جلب جميع الطلاب
$students = $conn->query("SELECT id, name FROM users WHERE role = 'student'");

// جلب جميع المواد
$courses = $conn->query("SELECT id, title FROM courses");

// جلب جميع عمليات التسجيل الحالية
$enrollments_query = $conn->prepare("
    SELECT 
        e.id, 
        u.name AS student_name, 
        c.title AS course_title
    FROM enrollments e
    JOIN users u ON e.student_id = u.id
    JOIN courses c ON e.course_id = c.id
    ORDER BY u.name, c.title
");
$enrollments_query->execute();
$enrollments = $enrollments_query->get_result();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>إدارة التسجيلات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <?php require_once '../includes/admin_navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-primary">🔗 إدارة تسجيل الطلاب في المواد</h2>
        </div>

        <div class="card shadow-lg mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">تسجيل جديد</h5>
            </div>
            <div class="card-body">
                <form action="enroll_process.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label for="student_id" class="form-label">اختر الطالب</label>
                            <select name="student_id" id="student_id" class="form-select" required>
                                <option value="">-- اختر طالباً --</option>
                                <?php while ($student = $students->fetch_assoc()): ?>
                                    <option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="course_id" class="form-label">اختر المادة</label>
                            <select name="course_id" id="course_id" class="form-select" required>
                                <option value="">-- اختر مادة --</option>
                                <?php while ($course = $courses->fetch_assoc()): ?>
                                    <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['title']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">تسجيل</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">التسجيلات الحالية</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>اسم الطالب</th>
                            <th>عنوان المادة</th>
                            <th>العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($enrollments->num_rows > 0): ?>
                            <?php while ($enrollment = $enrollments->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $enrollment['id'] ?></td>
                                    <td><?= htmlspecialchars($enrollment['student_name']) ?></td>
                                    <td><?= htmlspecialchars($enrollment['course_title']) ?></td>
                                    <td>
                                        <a href="unenroll.php?id=<?= $enrollment['id'] ?>" class="btn btn-sm btn-danger"
                                            onclick="return confirm('هل أنت متأكد من إلغاء تسجيل هذا الطالب؟')">إلغاء
                                            التسجيل</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">لا توجد تسجيلات حاليًا.</td>
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