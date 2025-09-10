<?php
require 'includes/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $password = trim($_POST['password']);
    $phone = trim($_POST['phone']);
    $role = 'student'; // يتم تعيين الدور تلقائياً كـ 'طالب'

    if (empty($name) || empty($password) || empty($phone)) {
        $error = "❌ جميع الحقول مطلوبة.";
    } else {
        // التحقق من أن اسم المستخدم غير موجود بالفعل
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE name = ?");
        $stmt_check->bind_param("s", $name);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check->num_rows > 0) {
            $error = "❌ اسم المستخدم موجود بالفعل، يرجى اختيار اسم آخر.";
        } else {
            // تشفير كلمة المرور
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // حفظ بيانات المستخدم الجديد
            $stmt_insert = $conn->prepare("INSERT INTO users (name, password, phone, role) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("ssss", $name, $hashed_password, $phone, $role);

            if ($stmt_insert->execute()) {
                header("Location: login.php?success=account_created");
                exit;
            } else {
                $error = "خطأ: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>إنشاء حساب جديد</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center justify-content-center vh-100">

    <div class="card shadow-lg p-4" style="max-width: 400px; width: 100%;">
        <h3 class="text-center mb-3">👤 إنشاء حساب جديد</h3>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger text-center"><?= $error ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success text-center">✅ تم إنشاء حسابك بنجاح! يمكنك الآن تسجيل الدخول.</div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">اسم المستخدم</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">كلمة المرور</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">الهاتف</label>
                <input type="text" name="phone" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">إنشاء حساب</button>
        </form>
        <hr>
        <div class="text-center mt-2">
            <a href="login.php" class="btn btn-link">لدي حساب بالفعل</a>
        </div>
    </div>

</body>

</html>