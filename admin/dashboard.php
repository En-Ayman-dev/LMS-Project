<?php
require '../includes/database.php';
require '../includes/auth.php';

// التحقق من صلاحيات الأدمن
check_role('admin');

// جلب جميع المستخدمين
$result = $conn->query("SELECT id, name, phone, role FROM users");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم الأدمن</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<?php require_once '../includes/admin_navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-primary">👨‍💻 لوحة تحكم الأدمن</h2>
            <div>
                <span class="me-3">مرحباً، <?= $_SESSION['name'] ?> (<?= $_SESSION['role'] ?>)</span>
                <a href="../logout.php" class="btn btn-outline-danger btn-sm">🚪 تسجيل الخروج</a>
            </div>
        </div>

        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">قائمة المستخدمين</h3>
            </div>
            <div class="card-body">
                <a href="create.php" class="btn btn-success mb-3">➕ إضافة مستخدم جديد</a>
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>الاسم</th>
                            <th>الهاتف</th>
                            <th>الدور</th>
                            <th>العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= $row['name'] ?></td>
                                <td><?= $row['phone'] ?></td>
                                <td><?= $row['role'] ?></td>
                                <td>
                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">✏ تعديل</a>
                                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                                        onclick="return confirm('هل أنت متأكد من الحذف؟')">🗑 حذف</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</html>