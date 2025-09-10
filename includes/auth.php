<?php
session_start();

// دالة للتحقق من أن المستخدم قام بتسجيل الدخول
function check_login() {
    if (!isset($_SESSION['id'])) {
        header("Location: /login.php");
        exit;
    }
}

// دالة للتحقق من دور المستخدم (مثلاً 'admin')
function check_role($required_role) {
    check_login(); // تأكد من تسجيل الدخول أولاً
    if ($_SESSION['role'] !== $required_role) {
        // إذا لم يكن الدور صحيحاً، أظهر رسالة خطأ وقم بتسجيل الخروج
        echo "<div style='padding:20px; color:red; font-weight:bold;'>❌ غير مصرح لك بالدخول إلى هذه الصفحة.</div>";
        echo "<a href='../logout.php'>تسجيل الخروج</a>";
        exit;
    }
}