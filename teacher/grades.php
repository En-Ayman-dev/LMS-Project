<?php
require '../includes/database.php';
require '../includes/auth.php';

// التحقق من صلاحيات المعلم
check_role('teacher');

$teacher_id = $_SESSION['id'];
$selected_course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : null;

// جلب جميع المواد التي يدرسها المعلم
$stmt_courses = $conn->prepare("SELECT id, title FROM courses WHERE teacher_id = ?");
$stmt_courses->bind_param("i", $teacher_id);
$stmt_courses->execute();
$courses_result = $stmt_courses->get_result();

$students_result = null;
$assignments_result = null;
$grades_data = [];

if ($selected_course_id) {
    // جلب جميع الطلاب المسجلين في المادة المحددة
    $stmt_students = $conn->prepare("
        SELECT u.id, u.name 
        FROM enrollments e 
        JOIN users u ON e.student_id = u.id 
        WHERE e.course_id = ?
    ");
    $stmt_students->bind_param("i", $selected_course_id);
    $stmt_students->execute();
    $students_result = $stmt_students->get_result();

    // جلب جميع الواجبات للمادة المحددة
    $stmt_assignments = $conn->prepare("SELECT id, title FROM assignments WHERE course_id = ?");
    $stmt_assignments->bind_param("i", $selected_course_id);
    $stmt_assignments->execute();
    $assignments_result = $stmt_assignments->get_result();

    // جلب جميع الدرجات للطلاب في هذه المادة
    $stmt_grades = $conn->prepare("
        SELECT s.student_id, s.assignment_id, s.grade 
        FROM submissions s
        JOIN assignments a ON s.assignment_id = a.id
        WHERE a.course_id = ?
    ");
    $stmt_grades->bind_param("i", $selected_course_id);
    $stmt_grades->execute();
    $grades_result = $stmt_grades->get_result();

    while ($grade = $grades_result->fetch_assoc()) {
        $grades_data[$grade['student_id']][$grade['assignment_id']] = $grade['grade'];
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>درجات الطلاب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<?php require_once '../includes/teacher_navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-primary">📝 درجات الطلاب</h2>
            <div>
                <span class="me-3">مرحباً، <?= $_SESSION['name'] ?> (<?= $_SESSION['role'] ?>)</span>
                <a href="../logout.php" class="btn btn-outline-danger btn-sm">🚪 تسجيل الخروج</a>
            </div>
        </div>

        <div class="mb-4">
            <form action="grades.php" method="GET">
                <label for="course_select" class="form-label">اختر المادة:</label>
                <select name="course_id" id="course_select" class="form-select" onchange="this.form.submit()">
                    <option value="">-- اختر مادة --</option>
                    <?php while ($course = $courses_result->fetch_assoc()): ?>
                        <option value="<?= $course['id'] ?>" <?= $selected_course_id == $course['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($course['title']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>

        <?php if ($selected_course_id && $students_result->num_rows > 0): ?>
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">سجل الدرجات</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>اسم الطالب</th>
                                    <?php
                                    $assignments_result->data_seek(0); // إعادة المؤشر للبداية
                                    $assignment_titles = [];
                                    while ($assignment = $assignments_result->fetch_assoc()) {
                                        $assignment_titles[$assignment['id']] = $assignment['title'];
                                        echo "<th>" . htmlspecialchars($assignment['title']) . "</th>";
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($student = $students_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($student['name']) ?></td>
                                        <?php foreach ($assignment_titles as $assignment_id => $title): ?>
                                            <td><?= $grades_data[$student['id']][$assignment_id] ?? 'N/A' ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php elseif ($selected_course_id && $students_result->num_rows === 0): ?>
            <div class="alert alert-info text-center">لا يوجد طلاب مسجلون في هذه المادة.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>