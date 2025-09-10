<?php
// إعدادات الاتصال بقاعدة البيانات
$host = "localhost";
$user = "root";
$pass = "";
$db   = "itcs3";

// إنشاء اتصال جديد
$conn = new mysqli($host, $user, $pass, $db);

// فحص الاتصال
if ($conn->connect_error) {
    // إيقاف التنفيذ وعرض رسالة خطأ آمنة (لا تكشف تفاصيل حساسة)
    die("فشل الاتصال: " . $conn->connect_error);
}