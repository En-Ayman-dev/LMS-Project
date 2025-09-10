<?php
require '../includes/database.php';
require '../includes/auth.php';

check_role('admin');

$id = intval($_GET['id']);

// جلب بيانات المستخدم الحالي
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $password = $_POST['password'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];

    // إذا كانت كلمة المرور جديدة، قم بتشفيرها
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name=?, password=?, phone=?, role=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $hashed_password, $phone, $role, $id);
    } else {
        // إذا كانت فارغة، لا تقم بتحديثها
        $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, role=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $phone, $role, $id);
    }

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit;
    } else {
        echo "خطأ: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>تعديل مستخدم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<?php require_once '../includes/admin_navbar.php'; ?>

    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-header bg-warning text-dark">
                <h3 class="mb-0">✏ تعديل بيانات المستخدم</h3>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">الاسم</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>"
                            class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">كلمة المرور (اتركها فارغة لعدم التغيير)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الهاتف</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>"
                            class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الدور</label>
                        <select name="role" class="form-select" required>
                            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>ادمن</option>
                            <option value="teacher" <?= $user['role'] == 'teacher' ? 'selected' : '' ?>>مدرس</option>
                            <option value="student" <?= $user['role'] == 'student' ? 'selected' : '' ?>>طالب</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-warning">💾 تحديث</button>
                    <a href="dashboard.php" class="btn btn-secondary">🔙 رجوع</a>
                </form>
            </div>
        </div>
    </div>

</body>

</html>