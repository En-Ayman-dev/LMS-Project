<?php
// قم بتفعيل عرض الأخطاء للمساعدة في تصحيح الأخطاء إذا لزم الأمر
ini_set('display_errors', 1);
error_reporting(E_ALL);

// تضمين ملف الاتصال بقاعدة البيانات
require 'includes/database.php';

// بيانات المستخدم الأدمن الافتراضي
$admin_name = 'admin';
$admin_password = 'Admin123'; // قم بتغيير كلمة المرور هذه إلى كلمة سر قوية بعد أول استخدام

// التحقق مما إذا كان حساب الأدمن موجودًا بالفعل
$stmt_check = $conn->prepare("SELECT id FROM users WHERE role = 'admin' AND name = ?");
$stmt_check->bind_param("s", $admin_name);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    echo "❌ حساب الأدمن موجود بالفعل. لا يمكن إنشاء حساب آخر.";
} else {
    // تشفير كلمة المرور
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

    // إدراج المستخدم الجديد
    $stmt_insert = $conn->prepare("INSERT INTO users (name, password, role) VALUES (?, ?, ?)");
    $stmt_insert->bind_param("sss", $admin_name, $hashed_password, $admin_name);

    if ($stmt_insert->execute()) {
        echo "✅ تم إنشاء حساب الأدمن بنجاح!<br>";
        echo "اسم المستخدم: <strong>" . htmlspecialchars($admin_name) . "</strong><br>";
        echo "كلمة المرور: <strong>" . htmlspecialchars($admin_password) . "</strong><br><br>";
        echo "<strong>⚠️ ملاحظة هامة جداً:</strong><br>";
        echo "لأسباب أمنية، يجب عليك <strong>حذف هذا الملف `setup_admin.php`</strong> فوراً من الخادم بعد استخدامه.";
    } else {
        echo "خطأ في إنشاء حساب الأدمن: " . $conn->error;
    }
}
?>