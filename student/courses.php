<?php
require '../includes/database.php';
require '../includes/auth.php';

// التحقق من صلاحيات الطالب
check_role('student');

$student_id = $_SESSION['id'];
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

if ($course_id === 0) {
    echo "❌ لم يتم تحديد المادة.";
    exit;
}

// التحقق من أن الطالب مسجل في هذه المادة
$stmt_enrollment = $conn->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
$stmt_enrollment->bind_param("ii", $student_id, $course_id);
$stmt_enrollment->execute();
$enrollment_result = $stmt_enrollment->get_result();

if ($enrollment_result->num_rows === 0) {
    echo "❌ أنت غير مسجل في هذه المادة.";
    exit;
}

// جلب تفاصيل المادة
$stmt_course = $conn->prepare("SELECT title, description FROM courses WHERE id = ?");
$stmt_course->bind_param("i", $course_id);
$stmt_course->execute();
$course = $stmt_course->get_result()->fetch_assoc();

// جلب الواجبات الخاصة بالمادة وتقديمات الطالب
$stmt_assignments = $conn->prepare("
    SELECT
        a.id,
        a.title,
        a.description,
        a.due_date,
        s.grade,
        s.file_path,
        s.id AS submission_id
    FROM assignments a
    LEFT JOIN submissions s ON a.id = s.assignment_id AND s.student_id = ?
    WHERE a.course_id = ?
");
$stmt_assignments->bind_param("ii", $student_id, $course_id);
$stmt_assignments->execute();
$assignments_result = $stmt_assignments->get_result();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($course['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <?php require_once '../includes/student_navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-primary"><?= htmlspecialchars($course['title']) ?></h2>
        </div>

        <div class="mb-3">
            <a href="dashboard.php" class="btn btn-secondary">🔙 العودة إلى لوحة التحكم</a>
        </div>

        <div class="card shadow-lg mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">وصف المادة</h5>
            </div>
            <div class="card-body">
                <p><?= nl2br(htmlspecialchars($course['description'])) ?></p>
            </div>
        </div>

        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">الواجبات</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>عنوان الواجب</th>
                            <th>تاريخ التسليم</th>
                            <th>حالة التقديم</th>
                            <th>درجتك</th>
                            <th>العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($assignments_result->num_rows > 0): ?>
                            <?php while ($assignment = $assignments_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($assignment['title']) ?></td>
                                    <td><?= $assignment['due_date'] ?></td>
                                    <td>
                                        <?php if ($assignment['submission_id']): ?>
                                            <span class="badge bg-success">تم التقديم</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">لم يتم التقديم</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $assignment['grade'] ?? 'غير مقيّم' ?></td>
                                    <td>
                                        <?php if (!$assignment['submission_id']): ?>
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#submitAssignmentModal"
                                                data-bs-assignment-id="<?= $assignment['id'] ?>">
                                                تقديم الواجب
                                            </button>
                                        <?php else: ?>
                                            <a href="<?= htmlspecialchars($assignment['file_path']) ?>"
                                                class="btn btn-sm btn-success" target="_blank">عرض ملفي</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">لا توجد واجبات لهذه المادة.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="submitAssignmentModal" tabindex="-1" aria-labelledby="submitAssignmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="submissionForm" action="submit_assignment.php?course_id=<?= $course_id ?>" method="POST"
                    enctype="multipart/form-data">
                    <input type="hidden" name="assignment_id" id="modalAssignmentId">
                    <div class="modal-header">
                        <h5 class="modal-title" id="submitAssignmentModalLabel">تقديم الواجب</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>اختر طريقة التقديم:</p>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="submission_type" id="uploadFileRadio"
                                value="file" checked>
                            <label class="form-check-label" for="uploadFileRadio">رفع ملف</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="submission_type" id="submitLinkRadio"
                                value="link">
                            <label class="form-check-label" for="submitLinkRadio">إرسال رابط</label>
                        </div>

                        <div id="fileInputContainer">
                            <label for="submissionFile" class="form-label">اختر ملف الواجب</label>
                            <input type="file" class="form-control" id="submissionFile" name="submission_file">
                        </div>

                        <div id="linkInputContainer" class="d-none">
                            <label for="submissionLink" class="form-label">أدخل رابط الواجب (مثال: رابط GitHub)</label>
                            <input type="url" class="form-control" id="submissionLink" name="submission_link">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">إرسال الواجب</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // JavaScript لتحديد الواجب الذي تم اختياره وتغيير طريقة التقديم
        document.addEventListener('DOMContentLoaded', function () {
            const submitModal = document.getElementById('submitAssignmentModal');
            const fileRadio = document.getElementById('uploadFileRadio');
            const linkRadio = document.getElementById('submitLinkRadio');
            const fileContainer = document.getElementById('fileInputContainer');
            const linkContainer = document.getElementById('linkInputContainer');
            const submissionFile = document.getElementById('submissionFile');
            const submissionLink = document.getElementById('submissionLink');

            submitModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const assignmentId = button.getAttribute('data-bs-assignment-id');
                const modalAssignmentIdInput = submitModal.querySelector('#modalAssignmentId');
                modalAssignmentIdInput.value = assignmentId;

                // Reset the form and default to file upload
                fileRadio.checked = true;
                fileContainer.classList.remove('d-none');
                linkContainer.classList.add('d-none');
                submissionFile.setAttribute('required', 'required');
                submissionLink.removeAttribute('required');
            });

            fileRadio.addEventListener('change', function () {
                if (this.checked) {
                    fileContainer.classList.remove('d-none');
                    linkContainer.classList.add('d-none');
                    submissionFile.setAttribute('required', 'required');
                    submissionLink.removeAttribute('required');
                }
            });

            linkRadio.addEventListener('change', function () {
                if (this.checked) {
                    linkContainer.classList.remove('d-none');
                    fileContainer.classList.add('d-none');
                    submissionLink.setAttribute('required', 'required');
                    submissionFile.removeAttribute('required');
                }
            });
        });
    </script>

</body>

</html>