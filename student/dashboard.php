<?php
require '../includes/database.php';
require '../includes/auth.php';

// Check for 'student' role
check_role('student');

// Get the logged-in student's ID
$student_id = $_SESSION['id'];

// Get all courses the student is enrolled in
$stmt = $conn->prepare("
    SELECT c.id, c.title, u.name AS teacher_name 
    FROM courses c
    JOIN enrollments e ON c.id = e.course_id
    JOIN users u ON c.teacher_id = u.id
    WHERE e.student_id = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم الطالب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<?php require_once '../includes/student_navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-primary">🧑‍🎓 لوحة تحكم الطالب</h2>
            <div>
                <span class="me-3">مرحباً، <?= $_SESSION['name'] ?> (<?= $_SESSION['role'] ?>)</span>
                <a href="../logout.php" class="btn btn-outline-danger btn-sm">🚪 تسجيل الخروج</a>
            </div>
        </div>

        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">المواد المسجل بها</h3>
            </div>
            <div class="card-body">
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
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= htmlspecialchars($row['teacher_name']) ?></td>
                                    <td>
                                        <a href="courses.php?course_id=<?= $row['id'] ?>" class="btn btn-sm btn-info">عرض
                                            وإدارة</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">لم يتم تسجيلك في أي مادة بعد.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>

</html>