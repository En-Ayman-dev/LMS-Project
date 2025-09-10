
<?php
session_start();

if (isset($_SESSION['id'])) {
  // المستخدم مسجل دخوله بالفعل، قم بتوجيهه بناءً على دوره
  if ($_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard.php");
  } else if ($_SESSION['role'] === 'teacher') {
    header("Location: teacher/dashboard.php");
  } else {
    header("Location: student/dashboard.php");
  }
  exit;
} else {
  // المستخدم غير مسجل دخوله، قم بتوجيهه إلى صفحة تسجيل الدخول
  header("Location: login.php");
  exit;
}