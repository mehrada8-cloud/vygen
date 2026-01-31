<?php
/** @var string $title */
/** @var string $content */
/** @var array $config */
?>
<!doctype html>
<html lang="fa" dir="rtl" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?> | پنل ادمین</title>
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body class="admin">
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="brand">
                <span class="brand-mark">VY</span>
                <div>
                    <strong>Vygen Admin</strong>
                    <p>Luxury Signals</p>
                </div>
            </div>
            <nav class="admin-nav">
                <a class="nav-item" href="/admin/categories">دسته‌بندی‌ها</a>
                <a class="nav-item" href="/admin/signals">سیگنال‌ها</a>
                <a class="nav-item" href="/categories" target="_blank" rel="noopener">نمایش عمومی</a>
            </nav>
            <div class="sidebar-actions">
                <button class="theme-toggle" data-theme-toggle>تغییر تم</button>
                <a class="ghost-link" href="/admin/logout">خروج امن</a>
            </div>
        </aside>
        <div class="admin-main">
            <header class="admin-topbar">
                <div>
                    <p class="app-label">پنل مدیریت</p>
                    <h1><?= htmlspecialchars($title) ?></h1>
                    <p class="subtle">مدیریت دقیق دسته‌بندی‌ها و سیگنال‌ها بدون خطا.</p>
                </div>
                <div class="topbar-actions">
                    <span class="status-pill">SSL فعال</span>
                    <span class="status-pill">PHP 8.2</span>
                </div>
            </header>
            <main class="admin-content">
                <?= $content ?>
            </main>
            <footer class="admin-footer">
                <span>Vygen Admin • Luxury Control Center</span>
            </footer>
        </div>
    </div>
    <script src="/assets/app.js" defer></script>
    <script src="/assets/admin.js" defer></script>
</body>
</html>
