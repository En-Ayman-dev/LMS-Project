<?php
require '../includes/database.php';
require '../includes/auth.php';

check_role('admin');

$course_id = intval($_GET['id']);

// جلب بيانات المادة الحالية
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    echo "❌ المادة غير موجودة.";
    exit;
}

// جلب قائمة المعلمين لعرضهم في النموذج
$teachers = $conn->query("SELECT id, name FROM users WHERE role = 'teacher'");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = $_POST['title'];
    $description = $_POST['description'];
    $teacher_id  = $_POST['teacher_id'];

    $stmt = $conn->prepare("UPDATE courses SET title = ?, description = ?, teacher_id = ? WHERE id = ?");
    $stmt->bind_param("ssii", $title, $description, $teacher_id, $course_id);

    if ($stmt->execute()) {
        header("Location: manage_courses.php");
        exit;
    } else {
        echo "خطأ في تعديل المادة: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تعديل المادة</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="card shadow-lg">
    <div class="card-header bg-warning text-dark">
      <h3 class="mb-0">✏ تعديل بيانات المادة</h3>
    </div>
    <div class="card-body">
      <form method="post">
        <div class="mb-3">
          <label class="form-label">عنوان المادة</label>
          <input type="text" name="title" value="<?= htmlspecialchars($course['title']) ?>" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">وصف المادة</label>
          <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($course['description']) ?></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">المدرس</label>
          <select name="teacher_id" class="form-select" required>
            <?php while($teacher = $teachers->fetch_assoc()): ?>
              <option value="<?= $teacher['id'] ?>" <?= $course['teacher_id'] == $teacher['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($teacher['name']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-warning">💾 تحديث</button>
        <a href="manage_courses.php" class="btn btn-secondary">🔙 رجوع</a>
      </form>
    </div>
  </div>
</div>

</body>
</html>