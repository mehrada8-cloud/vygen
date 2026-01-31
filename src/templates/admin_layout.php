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
    <div class="app-shell">
        <header class="site-header">
            <div>
                <p class="app-label">پنل مدیریت</p>
                <h1><?= htmlspecialchars($title) ?></h1>
                <p class="subtle">مدیریت دقیق دسته‌بندی‌ها و سیگنال‌ها بدون خطا.</p>
            </div>
            <div class="header-actions">
                <button class="theme-toggle" data-theme-toggle>تغییر تم</button>
                <a class="ghost-link" href="/admin/categories">دسته‌بندی‌ها</a>
                <a class="ghost-link" href="/admin/signals">سیگنال‌ها</a>
                <a class="ghost-link" href="/admin/logout">خروج</a>
            </div>
        </header>
        <main>
            <?= $content ?>
        </main>
        <footer class="site-footer">
            <span>Vygen Admin</span>
        </footer>
    </div>
    <script src="/assets/app.js" defer></script>
    <script src="/assets/admin.js" defer></script>
</body>
</html>
