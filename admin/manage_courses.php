<?php
require '../includes/database.php';
require '../includes/auth.php';

// التحقق من صلاحيات الأدمن
check_role('admin');

// جلب جميع المواد
$courses = $conn->query("SELECT c.*, u.name as teacher_name FROM courses c JOIN users u ON c.teacher_id = u.id");

// جلب جميع المعلمين لعرضهم في قائمة الاختيار
$teachers = $conn->query("SELECT id, name FROM users WHERE role = 'teacher'");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>إدارة المواد</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php require_once '../includes/admin_navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-primary">📚 إدارة المواد</h2>
            <div>
                <span class="me-3">مرحباً، <?= $_SESSION['name'] ?> (<?= $_SESSION['role'] ?>)</span>
                <a href="../logout.php" class="btn btn-outline-danger btn-sm">🚪 تسجيل الخروج</a>
            </div>
        </div>

        <!-- <div class="mb-3">
            <a href="dashboard.php" class="btn btn-info">إدارة المستخدمين</a>
            <a href="manage_courses.php" class="btn btn-primary">إدارة المواد</a>
        </div> -->

        <div class="card shadow-lg mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">قائمة المواد</h3>
            </div>
            <div class="card-body">
                <a href="#addCourseModal" class="btn btn-success mb-3" data-bs-toggle="modal">➕ إضافة مادة جديدة</a>
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>عنوان المادة</th>
                            <th>المدرس</th>
                            <th>العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $courses->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['teacher_name']) ?></td>
                                <td>
                                    <a href="edit_course.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">✏
                                        تعديل</a>
                                    <a href="delete_course.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                                        onclick="return confirm('هل أنت متأكد من الحذف؟')">🗑 حذف</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="add_course_process.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCourseModalLabel">إضافة مادة جديدة</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="courseTitle" class="form-label">عنوان المادة</label>
                            <input type="text" class="form-control" id="courseTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="courseDescription" class="form-label">وصف المادة</label>
                            <textarea class="form-control" id="courseDescription" name="description"
                                rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="courseTeacher" class="form-label">المدرس</label>
                            <select class="form-select" id="courseTeacher" name="teacher_id" required>
                                <?php while ($teacher = $teachers->fetch_assoc()): ?>
                                    <option value="<?= $teacher['id'] ?>"><?= htmlspecialchars($teacher['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ المادة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>