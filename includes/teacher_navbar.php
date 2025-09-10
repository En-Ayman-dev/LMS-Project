<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="../teacher/dashboard.php">لوحة تحكم المعلم</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="../teacher/dashboard.php">المواد المسندة</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../teacher/grades.php">درجات الطلاب</a>
                </li>
            </ul>
            <span class="navbar-text me-3 text-white">
                مرحباً، <?= $_SESSION['name'] ?>
            </span>
            <a href="../logout.php" class="btn btn-outline-danger">🚪 تسجيل الخروج</a>
        </div>
    </div>
</nav>